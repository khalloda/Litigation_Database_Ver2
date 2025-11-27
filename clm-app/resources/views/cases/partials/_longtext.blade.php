<div class="longtext-container">
    <div id="preview-{{ $id }}" dir="auto" class="text-wrap text-break">
        {{ nl2br(e($preview)) }}...
        <a href="#" class="show-more-link" data-target="full-{{ $id }}" data-preview="preview-{{ $id }}">
            {{ __('app.show_more') }}
        </a>
    </div>
    <div id="full-{{ $id }}" dir="auto" class="text-wrap text-break" style="display: none;">
        {{ nl2br(e($full)) }}
        <a href="#" class="show-less-link" data-target="full-{{ $id }}" data-preview="preview-{{ $id }}">
            {{ __('app.show_less') }}
        </a>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.show-more-link').forEach(function(link) {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('data-target');
            const previewId = this.getAttribute('data-preview');
            document.getElementById(previewId).style.display = 'none';
            document.getElementById(targetId).style.display = 'block';
        });
    });

    document.querySelectorAll('.show-less-link').forEach(function(link) {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('data-target');
            const previewId = this.getAttribute('data-preview');
            document.getElementById(targetId).style.display = 'none';
            document.getElementById(previewId).style.display = 'block';
        });
    });
});
</script>

