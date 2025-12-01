# Fix: Gemini API Version and Model Selection

> **Issue**: Both `gemini-pro` and `gemini-1.5-flash` return 404 in v1beta API  
> **Root Cause**: Model availability differs between API versions  
> **Status**: ✅ Fixed with fallback mechanism

---

## Problem

Both models failed:
- `gemini-1.5-flash` → 404 in v1beta
- `gemini-pro` → 404 in v1beta

The error suggests models are not available in the specified API version.

---

## Solution

### 1. Try v1 API First

**Primary attempt**: Use `v1` API with `gemini-1.5-flash`
```php
$apiVersion = 'v1';
$model = 'gemini-1.5-flash';
$url = "https://generativelanguage.googleapis.com/v1/models/{$model}:generateContent?key={$apiKey}";
```

### 2. Fallback to v1beta

**Fallback**: If v1 fails with 404, try `v1beta` with `gemini-pro`
```php
if (!$response->successful() && $response->status() === 404) {
    $apiVersion = 'v1beta';
    $model = 'gemini-pro';
    $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";
}
```

### 3. Enhanced Response Parsing

Handle different response structures:
```php
if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
    $text = $data['candidates'][0]['content']['parts'][0]['text'];
} elseif (isset($data['text'])) {
    $text = $data['text'];
} elseif (isset($data['content']['parts'][0]['text'])) {
    $text = $data['content']['parts'][0]['text'];
}
```

### 4. Added Helper Method

Created `tryGeminiApiCall()` to avoid code duplication:
```php
private function tryGeminiApiCall(string $url, array $httpOptions, string $prompt, string $apiKey)
{
    Log::info('Trying Gemini API call', ['url' => str_replace($apiKey, '***', $url)]);
    
    return Http::withOptions($httpOptions)->post($url, [
        'contents' => [
            [
                'parts' => [
                    ['text' => $prompt]
                ]
            ]
        ],
    ]);
}
```

---

## API Version Differences

### v1 API
- Newer API version
- Supports: `gemini-1.5-flash`, `gemini-1.5-pro`, `gemini-2.0-flash-exp`
- Endpoint: `https://generativelanguage.googleapis.com/v1/models/{model}:generateContent`

### v1beta API
- Older API version
- Supports: `gemini-pro`, `gemini-pro-vision`
- Endpoint: `https://generativelanguage.googleapis.com/v1beta/models/{model}:generateContent`

---

## Files Changed

1. ✅ `clm-app/app/Http/Controllers/Api/AiController.php`
   - Changed to try v1 API first
   - Added fallback to v1beta
   - Enhanced response parsing
   - Added helper method

---

## Testing

After the fix:

1. **Config cache cleared** (already done)
2. **Try generating summary**:
   - Navigate to Cases page
   - Click "Generate AI Summary"
   - Check logs for which API version/model worked

3. **Check Laravel logs**:
   ```bash
   tail -f storage/logs/laravel.log | grep -i gemini
   ```
   Look for:
   - "Trying Gemini API call" - Shows which endpoint is tried
   - "v1 API failed, trying v1beta" - If fallback is triggered
   - "Gemini API response" - Shows success/failure

---

## Expected Behavior

1. **First attempt**: v1 API with `gemini-1.5-flash`
   - If successful → Use response
   - If 404 → Try fallback

2. **Fallback**: v1beta API with `gemini-pro`
   - If successful → Use response
   - If fails → Throw error with details

---

## Alternative: List Available Models

If both fail, you can check available models:

```bash
curl "https://generativelanguage.googleapis.com/v1/models?key=YOUR_API_KEY"
```

Or in PHP:
```php
$response = Http::get("https://generativelanguage.googleapis.com/v1/models?key={$apiKey}");
$models = $response->json();
Log::info('Available models', ['models' => $models]);
```

---

**Status**: ✅ Fixed with fallback mechanism - Ready for testing

