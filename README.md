<h1><p align="center">GiveWP - Visual Donation Form Builder 💚</p></h1>

<p align="center">🚨 Warning this project is a work in progress and should not be used on a live site to accept donations. With that disclaimer out of the way, let's answer the question of what the heck this is. The goal of this project is to provide GiveWP admins complete flexibility over the donation form creation process via a brand new visual donation builder.</p>

---

## The Why

GiveWP is developing a solution to allow fundraisers to create their donation forms in a more powerful, drag-and-drop, visual way.

At the core of the project is a new interface designed to give administrators new tools to create and edit their donation forms. It will be similar to other form builders, but specifically tailored to the unique needs of nonprofit organizations.

In this new interface admins will be able to add custom fields to various locations of their donation form and map the corresponding custom data to either donor or donation metadata. The default required fields for donation forms (first and last name, email, and relevant payment fields) will be customizable by allowing label changes, placeholder text, tooltips, and more.

To achieve this new functionality the team is engaging in UX/UI design and development, frontend and backend database development, and an extensive overhaul of the payment gateways API.

## Development

Want to help contribute? Awesome! We're always looking for new contributors to help us out.

### Getting Set Up
1. Clone this repository locally in your `wp-content/plugins/` directory.
2. Run `composer install` from the CLI
3. Run `npm install` from the CLI

### Asset Compilation
To compile your CSS & JS assets, run one of the following:
- `npm run dev` — Compiles all assets for development one time
- `npm run watch` — Compiles all assets for development one time and then watches for changes, supporting [BrowserSync](https://laravel-mix.com/docs/5.0/browsersync)
- `npm run hot` — Compiles all assets for development one time and then watches for [hot replacement](https://laravel-mix.com/docs/5.0/hot-module-replacement)
- `npm run dev` — Compiles all assets for production one time

## Packages

Parts of the codebase are separated out into packages, making the repository a "monorepo". This allows for related functionality to be maintained in the same repository, but allows for technical differences in development.

Packages are managed using NPM "workspaces" and NPM commands can be passed to individial packages using the `-w` flag and are namespaced as `@givewp/{package-name}`.

### Form Builder - `@givewp/form-builder`

The Form Builder package, also known internally as Givenberg, uses WordPress Gutenberg components to create a custom block editor (not to be confused with THE WordPress Block Editor, which is the main product built with the Gutenberg components).

Local development uses Create React App and can be run outside of the context of WordPress.

To develop the Form Builder locally run `npm run start -w @givewp/form-builder` to start the local environment.

To build the Form Builder for use in the context of WordPress run `npm run build -w @givewp/form-builder`. This will create a build inside of the plugin which is used by the corresponding domain in the `src/FormBuilder` directory.
