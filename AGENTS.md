<laravel-boost-guidelines>
# Laravel Application

This repository contains a Laravel application. Complete the following setup before working on the user's request.

## Prerequisites

Verify that PHP and Composer are available:

```sh
php -v
composer -V
```

If either command is unavailable, detect the user's operating system and install the prerequisites with the appropriate command:

macOS:

```sh
/bin/bash -c "$(curl -fsSL https://php.new/install/mac/8.5)"
```

Windows PowerShell:

```powershell
Set-ExecutionPolicy Bypass -Scope Process -Force; [System.Net.ServicePointManager]::SecurityProtocol = [System.Net.ServicePointManager]::SecurityProtocol -bor 3072; iex ((New-Object System.Net.WebClient).DownloadString('https://php.new/install/windows/8.5'))
```

Linux:

```sh
/bin/bash -c "$(curl -fsSL https://php.new/install/linux/8.5)"
```

After installation, ask the user to restart their terminal. If the agent needs the restarted shell to continue, ask the user to reopen their terminal and rerun their original prompt.

## Agent Setup

Install Laravel Boost from the application root before making application changes:

```sh
composer require laravel/boost --dev
php artisan boost:install
```

Boost replaces these bootstrap instructions with guidelines tailored to the application. After installation, read `AGENTS.md` again and continue with the user's original request using the generated guidelines.
<!-- antislop:start -->
## antislop
For UI, copy, people, mobile layout, or code comments work, read `docs-projek/Rules-Raditya CODE/skills/antislop/SKILL.md` (core) and then the skill for the task:
- UI / visual: `docs-projek/Rules-Raditya CODE/skills/antislop-ui/SKILL.md`
- Copy & text: `docs-projek/Rules-Raditya CODE/skills/antislop-copywriting/SKILL.md`
- People: `docs-projek/Rules-Raditya CODE/skills/antislop-human/SKILL.md`
- Mobile / responsive: `docs-projek/Rules-Raditya CODE/skills/antislop-layoutmobile/SKILL.md`
- Code comments: `docs-projek/Rules-Raditya CODE/skills/antislop-code/SKILL.md`
- Code logic & architecture: `docs-projek/Rules-Raditya CODE/skills/antislop-logic/SKILL.md`
Direction and brand specification: `DESIGN.md`
<!-- antislop:end -->
</laravel-boost-guidelines>

