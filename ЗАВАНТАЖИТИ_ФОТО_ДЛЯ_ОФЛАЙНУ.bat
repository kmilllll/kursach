@echo off
chcp 65001 >nul
cd /d "%~dp0"
if not exist "media\pexels" mkdir "media\pexels"
powershell -NoProfile -ExecutionPolicy Bypass -Command "$ProgressPreference='SilentlyContinue'; $ids=@(19027765,20607952,5490390,16244984); foreach($id in $ids){$url='https://images.pexels.com/photos/'+$id+'/pexels-photo-'+$id+'.jpeg?auto=compress&cs=tinysrgb&w=1100'; $dst='media/pexels/bouquet-'+$id+'.jpg'; try{Invoke-WebRequest -Uri $url -OutFile $dst -UseBasicParsing; Write-Host ('OK bouquet '+$id)}catch{Write-Host ('ERROR '+$id+': '+$_.Exception.Message)}}; $ids=@(5706021,4397882,5485990); foreach($id in $ids){$url='https://images.pexels.com/photos/'+$id+'/pexels-photo-'+$id+'.jpeg?auto=compress&cs=tinysrgb&w=1100'; $dst='media/pexels/gift-'+$id+'.jpg';try{Invoke-WebRequest -Uri $url -OutFile $dst -UseBasicParsing;Write-Host ('OK gift '+$id)}catch{Write-Host ('ERROR '+$id+': '+$_.Exception.Message)}}"
echo.
echo Photos are ready if entries say OK. If not, check your Internet connection.
pause
