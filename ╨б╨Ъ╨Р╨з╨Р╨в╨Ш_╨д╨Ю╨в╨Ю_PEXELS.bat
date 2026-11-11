@echo off
chcp 65001 >nul
cd /d "%~dp0"
if not exist "media\pexels" mkdir "media\pexels"
echo Saving real Pexels flower photos locally for offline XAMPP use ...
powershell -NoProfile -ExecutionPolicy Bypass -Command "$ErrorActionPreference='Continue'; $sets=@(@('bouquet',@(31265182,34701051,23731967,16244984,28759039)),@('gift',@(1233440,36106446,31870268,35285625))); foreach($set in $sets){$kind=$set[0]; foreach($id in $set[1]){$f='media/pexels/'+$kind+'-'+$id+'.jpg'; try {Invoke-WebRequest -Uri ('https://images.pexels.com/photos/'+$id+'/pexels-photo-'+$id+'.jpeg?w=1200') -OutFile $f -UseBasicParsing; if((Get-Item $f).Length -lt 10000){throw 'Too small'}; Write-Host ('OK '+$f)} catch{if(Test-Path $f){Remove-Item $f -ErrorAction SilentlyContinue}; Write-Host ('FAILED '+$kind+' '+$id)}}}"
echo.
echo If any download failed, the website will try Pexels online links.
pause
