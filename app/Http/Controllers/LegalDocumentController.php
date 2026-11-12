<?php

namespace App\Http\Controllers;

use App\Models\LegalDocumentVersion;
use App\Services\LegalDocumentService;
use Illuminate\View\View;

class LegalDocumentController extends Controller
{
    /**
     * @var array<string, array{title: string, description: string}>
     */
    private const PAGE_META = [
        LegalDocumentVersion::TERMS => [
            'title' => 'Terms & Conditions — Xplore Smithers',
            'description' => 'Terms and Conditions for the Xplore Smithers app. Review the rules, disclaimers, and policies that govern your use of the App.',
        ],
        LegalDocumentVersion::PRIVACY => [
            'title' => 'Privacy Policy — Xplore Smithers Trail Finder',
            'description' => 'Privacy Policy for Xplore Smithers Trail Finder. Learn how we collect, use, and protect your information.',
        ],
    ];

    public function __construct(private LegalDocumentService $legalDocuments) {}

    public function show(string $type): View
    {
        $document = $this->legalDocuments->current($type);

        abort_if($document === null, 404);

        return view('legal.show', [
            'document' => $document,
            'html' => $this->legalDocuments->renderDocument($document),
            'pageTitle' => self::PAGE_META[$type]['title'],
            'metaDescription' => self::PAGE_META[$type]['description'],
        ]);
    }
}
