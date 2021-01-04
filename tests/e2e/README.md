# GiveWP e2e testing

End-to-end testing (e2e) checks whether GiveWP's features display and function as intended. These tests focus on the output of the webpage and interactions with it.

## How to run the tests?

### Prerequisites
These tests run inside a dockerized container, so it is necessary that you have the [Docker Engine](https://docs.docker.com/install/) and [docker-compose](https://docs.docker.com/compose/install/) installed on your system. For a quick installation from scratch, you can simply install the [Docker Desktop app](https://www.docker.com/products/docker-desktop).

A sample `wordpress.sql` is provided within the `/sample-data/` folder that has a few sample forms, donations, and donors to test in various combinations.

### Testing

After setting up the local development environment, running tests manually is fairly simple. Navigate to the Give root folder and get things started by running:

```npm run test:e2e```

This will use `wp-env` to spin up a new local version of WordPress with your current changes to GiveWP. Once setup, Cypress is then opened, and ready to run tests against it.

When you're done with testing, simply close the Cypress GUI, and run `npm run wp-env stop` to stop the WP instance that was created.

### Learn More
You can find more information about what e2e testing achieves, how e2e tests are implemented in GiveWP, and how to write your own tests via the [GiveWP dev manual](https://app.gitbook.com/@give/s/givewp/testing/types-of-tests/end-to-end-testing). Happy testing!
