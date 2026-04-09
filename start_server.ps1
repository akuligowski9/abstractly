Write-Host "Starting Laravel Backend..."
Start-Process "bin\php\php.exe" -ArgumentList "artisan serve" -WindowStyle Normal

Write-Host "Starting Vite Frontend..."
Start-Process "npm" -ArgumentList "run dev" -WindowStyle Normal

Write-Host "Servers started in new windows. Please keep them open while developing."
