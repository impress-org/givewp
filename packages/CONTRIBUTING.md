# GiveWP Workspace Packages

## Workspace Package Scripts

Scripts in Workspace packages can be run by appending a `--workspace` (`-w`) argument to the command.

`npm run start --workspace={PACKAGE_NAME}`
`npm run start --workspace=@givewp/form-builder`

`npm run start -w {PACKAGE_NAME}`
`npm run start -w @givewp/form-builder`

Additionally, scripts with a shared name can be run across all Workspaces by appending the `--workspaces` (plural) command. Note that this does not include the script in the root package.

`npm run build --workspaces`

