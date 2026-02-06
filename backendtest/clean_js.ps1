$content = Get-Content 'backend/js/stockproductname.js'
$cleanContent = $content | Where-Object { $_ -notmatch '^\s*<script' -and $_ -notmatch '^\s*</script' }
$cleanContent | Set-Content 'backend/js/stockproductname.js'
