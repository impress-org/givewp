# Unit Tests

"Pure" (I use that term loosly) unit tests represent a single "unit" of code, usually a function or a class, which is tested in isolation, meaning that WordPress is not involved.

## Directory Structure

Tests files should match the directory structure of the `src/` directory.

For example, the Settings Repositroy for the Onboarding domain is located at `tests/unit/Onboarding/SettingsRepositoryTest.php", which matches the tested class at `src/Onboarding/SettingsRepository.php`.