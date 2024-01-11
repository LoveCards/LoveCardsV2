@echo off
setlocal

set "directory=C:\Users\76814\Desktop\LoveCards-v2.1.1"

for /d %%d in ("%directory%\*") do (
    echo %%~nxd
)

for %%f in ("%directory%\*") do (
    if not "%%~df"=="%directory%\" (
        echo %%~nxf
    )
)

endlocal
