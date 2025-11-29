<div class="footer">
    <div>
        {{ ($locale ?? 'ar') === 'ar' ? 'تاريخ الإصدار' : 'Generated At' }}: 
        {{ $generatedAt->format(($locale ?? 'ar') === 'ar' ? 'Y-m-d H:i' : 'Y-m-d H:i') }}
    </div>
    @if(isset($pageNumbers) && $pageNumbers)
        <div>
            {{ ($locale ?? 'ar') === 'ar' ? 'الصفحة' : 'Page' }} <span class="page"></span> 
            {{ ($locale ?? 'ar') === 'ar' ? 'من' : 'of' }} <span class="topage"></span>
        </div>
    @endif
</div>

