
<# PowerShell like a boss
#>

Remove-Variable * -ErrorAction SilentlyContinue; Remove-Module *; $error.Clear(); Clear-Host

Function Test-NpmInPath {
    [CmdletBinding()]
    Param (
    )

    PROCESS {
        try {
            Get-Command npm -ErrorAction:Stop
        } catch {
            Write-Output $false
            return
        }

        Write-Output $true
    }
}

Function Test-Installed {
    [CmdletBinding()]
    Param (
        [Parameter (Mandatory=$true)]
        [String] $Exe,
        [Parameter (Mandatory=$true)]
        [String] $Target
    )

    PROCESS {
        try {
            Get-Command npm $Target -ErrorAction:Stop
        } catch {
            Write-Output $_.Exception.Message # $false
            return
        }

        Write-Output $true
    }
}

Function Invoke-Cmd {
    [CmdletBinding()]
    Param (
        [Parameter (Mandatory=$true)]
        [String] $Exe,
        [Parameter (Mandatory=$true)]
        [String] $Args
    )

    PROCESS {
            Write-Verbose "Running"
            & cmd /C "$Exe $Args" | Out-Null
        }
}

Function Invoke-InstallIfMissing {
    [CmdletBinding()]
    Param (
        [Parameter (Mandatory=$true)]
        [String] $Exe,
        [Parameter (Mandatory=$true)]
        [String] $Args,
        [Parameter (Mandatory=$true)]
        [String] $Target
    )

    PROCESS {
            if (!(Test-Installed -Exe:$Exe -Target:$Target)) {
                
                Write-Output "cmd'ing $Target"

                Invoke-Cmd -Exe:$Exe -Args:$Args
            }
            else {
                Write-Output "$Target installed"
            }
        }
}

if (!(Test-NpmInPath)) {

	Write-Output 'Installing Node.js...'
	npm install
}
else {
	Write-Output 'Updating Node.js Packages...'
	npm update
}

$Target = 'gulp'
Invoke-InstallIfMissing -Exe:'npm' -Args:"install -g $Target" -Target:$Target

$Target = 'cucumber'
Invoke-InstallIfMissing -Exe:'npm' -Args:"install -g $Target" -Target:$Target

$Target = 'gulp-cucumber'
Invoke-InstallIfMissing -Exe:'npm' -Args:"install $Target" -Target:$Target

$Target = 'gulp-notify'
Invoke-InstallIfMissing -Exe:'npm' -Args:"install $Target" -Target:$Target

$Target = 'gulp-watch'
Invoke-InstallIfMissing -Exe:'npm' -Args:"install $Target" -Target:$Target

$Target = 'scss-lint'
Invoke-InstallIfMissing -Exe:'gem' -Args:"install $Target" -Target:$Target

gulp