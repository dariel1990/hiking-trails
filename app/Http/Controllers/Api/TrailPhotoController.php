<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreTrailPhotoRequest;
use App\Http\Resources\TrailPhotoResource;
use App\Models\TrailPhoto;
use App\Models\User;
use App\Notifications\NewTrailPhotoSubmitted;
use App\Services\RecaptchaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class TrailPhotoController extends Controller
{
    private const FULL_WIDTH = 1600;

    private const FULL_HEIGHT = 900;

    private const THUMB_WIDTH = 640;

    private const THUMB_HEIGHT = 360;

    private const EMAIL_DAILY_LIMIT = 5;

    public function __construct(private RecaptchaService $recaptcha) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $trailId = $request->integer('trail_id');

        $photos = TrailPhoto::query()
            ->approved()
            ->when($trailId, fn ($q) => $q->where('trail_id', $trailId))
            ->whereNotNull('image_path')
            ->latest()
            ->limit(48)
            ->get()
            ->filter(fn (TrailPhoto $photo) => Storage::disk('public')->exists($photo->image_path))
            ->values();

        return TrailPhotoResource::collection($photos);
    }

    public function store(StoreTrailPhotoRequest $request): JsonResponse
    {
        $email = strtolower($request->string('email')->value());

        if ($this->hasExceededEmailQuota($email)) {
            return response()->json([
                'message' => 'You have reached the submission limit for today. Please try again tomorrow.',
            ], Response::HTTP_TOO_MANY_REQUESTS);
        }

        $tokenValid = $this->recaptcha->verify(
            $request->input('g-recaptcha-response'),
            'submit_trail_photo',
            $request->ip(),
        );

        if (! $tokenValid) {
            return response()->json([
                'message' => 'Spam check failed. Please refresh the page and try again.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        /** @var UploadedFile $upload */
        $upload = $request->file('image');
        $trailId = (int) $request->input('trail_id');

        [$imagePath, $thumbnailPath] = $this->processAndStore($upload, $trailId);

        $photo = TrailPhoto::create([
            'trail_id' => $trailId,
            'image_path' => $imagePath,
            'thumbnail_path' => $thumbnailPath,
            'caption' => $request->input('caption'),
            'name' => $request->input('name'),
            'email' => $email,
            'submitter_ip' => $request->ip(),
            'status' => TrailPhoto::STATUS_PENDING,
        ]);

        $this->bumpEmailQuota($email);
        $this->notifyAdmins($photo);

        return response()->json([
            'message' => 'Thanks — your photo is awaiting review.',
            'id' => $photo->id,
        ], Response::HTTP_CREATED);
    }

    /**
     * Re-encode the upload to WebP (stripping EXIF) and write the
     * full image plus a 16:9 thumbnail to the public disk.
     *
     * @return array{0: string, 1: string}
     */
    private function processAndStore(UploadedFile $upload, int $trailId): array
    {
        $manager = new ImageManager(new Driver);

        $image = $manager->read($upload->getRealPath());
        $image->cover(self::FULL_WIDTH, self::FULL_HEIGHT);

        $thumb = $manager->read($upload->getRealPath());
        $thumb->cover(self::THUMB_WIDTH, self::THUMB_HEIGHT);

        $uuid = (string) Str::uuid();
        $imagePath = "trail-photos/{$trailId}/{$uuid}.webp";
        $thumbPath = "trail-photos/{$trailId}/thumbs/{$uuid}.webp";

        Storage::disk('public')->put($imagePath, (string) $image->toWebp(85));
        Storage::disk('public')->put($thumbPath, (string) $thumb->toWebp(80));

        return [$imagePath, $thumbPath];
    }

    private function notifyAdmins(TrailPhoto $photo): void
    {
        $admins = User::query()->where('is_admin', true)->get();

        if ($admins->isNotEmpty()) {
            Notification::send($admins, new NewTrailPhotoSubmitted($photo));
        }
    }

    private function hasExceededEmailQuota(string $email): bool
    {
        return Cache::get($this->emailQuotaKey($email), 0) >= self::EMAIL_DAILY_LIMIT;
    }

    private function bumpEmailQuota(string $email): void
    {
        $key = $this->emailQuotaKey($email);
        $current = Cache::get($key, 0);
        Cache::put($key, $current + 1, now()->addDay());
    }

    private function emailQuotaKey(string $email): string
    {
        return 'trail-photo-quota:'.sha1($email);
    }
}
