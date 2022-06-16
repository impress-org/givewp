# GiveWP - Test Data

## Introduction

Generate test data using CLI

### Generating test data

There is a specific order which you have to follow when generating the test data

1. Generate Donation Forms
    - *If there are no Donation Forms available on the site, it's important to generate them first*

2. Generate Donors

3. Generate Donations

## CLI Commands

### Generate Donations

`wp give test-donation-form`

**Options**

`[--count=<count>]`

 Number of donations to generate

 default: `10`


`[--template=<template>]`

 Form template

 default: `random`

 options:
 - `sequoia`
 - `legacy`
 - `random`

 `[--set-goal=<bool>]`

 Set donation form goal

 default: `false`

`[--set-terms=<bool>]`

 Set donation form terms and conditions

 default: `false`

`[--preview=<preview>]`

Preview generated data

default: `false`

`[--consistent=<consistent>]`

Make generated data consistent

default: `false`


**Example usage**

 `wp give test-donation-form --count=10 --template=legacy --set-goal=true --set-terms=true --consistent=true`


 **Help**

 `wp help give test-donation-form`


 ### Generate Donors

 `wp give test-donors`

 **Options**

`[--count=<count>]`

Number of donors to generate

default: `10`

`[--preview=<preview>]`

Preview generated data

default: `false`

`[--consistent=<consistent>]`

Make generated data consistent

default: `false`

**Example usage**

`wp give test-donors --count=10 --preview=true --consistent=true`

 **Help**

 `wp help give test-donors`


 ### Generate Donations

 `wp give test-donations`

 **Options**

`[--count=<count>]`

Number of donations to generate

default: `10`

`[--status=<status>]`

Donation status

default: `publish`

options:
- `publish`
- `pending`
- `refunded`
- `cancelled`
- `random`

Get all available statuses with command:

`wp give test-donation-statuses`


`[--total-revenue=<amount>]`

Total revenue amount to be generated

default: `0`

`[--preview=<preview>]`

Preview generated data

default: `false`

`[--start-date=<date>]`

Set donation start date. Date format is `YYYY-MM-DD`

default: `false`

`[--params=<params>]`

Used for passing additional parameters

Example usage:

`--params=donation_currency=EUR\&donation_cover_fees=true`

default: `''`

`[--consistent=<consistent>]`

Make generated data consistent

default: `false`

### Generate GiveWP demonstration page

`wp give test-demonstration-page`

Generates GiveWP demonstration page with all GiveWP shortcodes included

 **Options**

`[--preview=<preview>]`

Preview generated data

default: `false`

**Example usage**

`wp give test-demonstration-page --count=10 --preview=true`

 **Help**

 `wp help give test-demonstration-page`
