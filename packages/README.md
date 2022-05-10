# GiveWP Workspace Packages

GiveWP uses NPM workspaces to manage a monorepo in which multiple packages
are maintained as a single repository.

## When to add a workspace package

Normally, javascript related work should happen inside a domain folder, within
the `sr/{DOMAIN}` directory.

However, there are some instances that require separation from the root
`package.json`, its dependency management, or its build process. These
instances are not common.

### Use Case: The project is maintained as a dependency of the main project.

Not all code of an application is specific to the business logic of the main
project and can be reasonably extracted as a dependency, creating a boundary
between the main project and the dependency. This boundary can be beneficial
for clean architecture and/or maintaining the option to extract the package
as an external dependency all together.

### Use Case: The project requires a different build process than the main project.

When conflicts arise that are not reasonably resolved between two parts of
the main project and thus require separate dependencies or a separate build
process, one part of the project can be extracted as a local dependency and
maintained as a momo-repo. Such code is likely coupled to the main project
but has some requirement that is in conflict with the main project.

The Form Builder package is a prime example of a conflict that is not
reasonably resolved. Specifically, the Form Builder requires dependency
management outside the norm for WordPress block development. When developing
blocks for WordPress, the environment shared by plugins requires that shared
dependencies across plugins be extracted and referenced off the window as
provided by the Block Editor. By contrast, the Form Builder is a custom
editor and uses some of the same package dependencies, but requires that
these dependencies not be extracted because there is no shared environment.
