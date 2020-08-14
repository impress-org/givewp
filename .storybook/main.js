module.exports = {
    stories: [ // Custom loading order.
      '../src/Views/Admin/Pages/templates/setup-page/stories/examples.stories.js',
      '../src/Views/Admin/Pages/templates/setup-page/stories/components.stories.js',
      '../src/Views/Admin/Pages/templates/setup-page/stories/items.stories.js',
      '../src/Views/Admin/Pages/templates/setup-page/stories/components.stories.js',
      '../src/Views/Admin/Pages/templates/setup-page/stories/icons.stories.js',
      '../src/Views/Admin/Pages/templates/setup-page/stories/**/*.stories.js', // Catchall
    ],
    addons: [
      '@storybook/addon-a11y/register',
    ],
  };
  