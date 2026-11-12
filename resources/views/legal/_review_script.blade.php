<script>
    function legalReview(documentCount) {
        return {
            step: 0,
            read: Array(documentCount).fill(false),
            agreed: Array(documentCount).fill(false),
            get isLastStep() {
                return this.step === documentCount - 1;
            },
            get canContinue() {
                return this.isLastStep ? this.agreed.every(Boolean) : this.agreed[this.step];
            },
            checkScrolledToEnd() {
                const panel = this.$refs['panel' + this.step];

                if (! panel || panel.clientHeight === 0) {
                    return;
                }

                if (panel.scrollTop + panel.clientHeight >= panel.scrollHeight - 24) {
                    this.read[this.step] = true;
                }
            },
            show(step) {
                this.step = step;
                this.$nextTick(() => this.checkScrolledToEnd());
            },
            continueReview() {
                if (! this.canContinue) {
                    return;
                }

                if (this.isLastStep) {
                    window.dispatchEvent(new CustomEvent('legal-review-complete'));
                } else {
                    this.show(this.step + 1);
                }
            },
        };
    }
</script>
