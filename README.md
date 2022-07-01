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
