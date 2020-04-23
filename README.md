# Mars ACSF Platform

[![Build Status](https://marsdevteam.visualstudio.com/MarsExperiencePlatform/_apis/build/status/New%20Stack?branchName=master)](https://marsdevteam.visualstudio.com/MarsExperiencePlatform/_build/latest?definitionId=2092&branchName=master)

This repository contains the code to support the Mars ACSF project - 2020 stack

# Getting Started

This project is based on BLT, an open-source project template and tool that enables building, testing, and deploying Drupal installations following Acquia Professional Services best practices. While this is one of many methodologies, it is our recommended methodology.

* Review the [Required / Recommended Skills](https://docs.acquia.com/blt/developer/skills/) for working with a BLT project.

* Ensure that your computer meets the minimum installation requirements (and then install the required applications). See the [BLT System Requirements](https://docs.acquia.com/blt/install/) and [DDev System Requirements](https://ddev.readthedocs.io/en/stable/#system-requirements).

* Request access to organization that owns the project repo in GitHub (if needed).

* Fork the project repository in GitHub.

* Request access to the Acquia Cloud Environment for your project (if needed).

* Setup a SSH key that can be used for GitHub and the Acquia Cloud (you CAN use the same key).

  * [Setup GitHub SSH Keys](https://help.github.com/articles/adding-a-new-ssh-key-to-your-github-account/)
  * [Setup Acquia Cloud SSH Keys](https://docs.acquia.com/acquia-cloud/ssh/generate)

* Clone your forked repository. By default, Git names this "origin" on your local.

```
$ git clone git@github.com:digital-experience-platform/acsf.git
```

* To ensure that upstream changes to the parent repository may be tracked, add the upstream locally as well.

```
$ git remote add upstream git@github.com:digital-experience-platform/acsf.git
```

----

# Setup Local Environment

BLT provides an automation layer for testing, building, and launching Drupal 8 applications. For ease when updating codebase it is recommended to use [DDev](https://ddev.readthedocs.io/en/stable/).

## Install Composer dependencies

After you have forked, install Composer Dependencies. (Warning: may take several minutes.)

```
ddev composer install
```

## Setup Docker

Setup the VM with the configuration from this repository's [configuration files](#important-configuration-files).

```
ddev start
```

## Setup a local blt alias.

```
ddev composer run-script `./vendor/bin/blt blt:init:shell-alias`
```

*You may encounter issues with this script on  Windows, if so try to run inside of the docker container:*

```
ddev ssh
composer run-script `./vendor/bin/blt blt:init:shell-alias`
```

* Setup a local Drupal site with an empty database.

```
ddev blt setup
```

* Log into your site with drush, access the site and do necessary work at #LOCAL_DEV_URL by running the following commands.

```
ddev drush uli
```

---

## (Optional) Other Local Setup Steps

* Set up frontend build and theme.
By default BLT sets up a site with the lightning profile and a cog base theme. You can choose your own profile before setup in the blt.yml file. If you do choose to use cog, see [Cog's documentation](https://github.com/acquia-pso/cog/blob/8.x-1.x/STARTERKIT/README.md#create-cog-sub-theme) for installation.
See [BLT's Frontend docs](https://docs.acquia.com/blt/developer/frontend/) to see how to automate the theme requirements and frontend tests.
After the initial theme setup you can configure `blt/blt.yml` to install and configure your frontend dependencies with `blt setup`.

* Pull Files locally, Use BLT to pull all files down from your Cloud environment.

```
ddev blt drupal:sync:files
```

* Sync the Cloud Database.
If you have an existing database you can use BLT to pull down the database from your Cloud environment.

```
ddev blt sync
```

---

## Local frontend build

Please reference readme in the custom themes for building sass frontend assets.

To install npm dependencies:

```
npm install npm@latest -g
```

---

# Commit Naming convention

Commits - Use AB#XXX in your commit message to link from GitHub to Azure Boards work items. For example, AB#125 will link to Azure work item ID 125.

Example:

```
AB#125: Carousel structure.
```
In addition, you can enter a commit or pull request message to transition the work item. The system will recognize `fix`, `fixes`, `fixed` and *apply* it to the #-mention item that follows. Some examples are provided as shown.

## Examples:

Commit message                                         |	Action
------------------------------------------------------ | -------
Fixed AB#123                                           |	Links and transitions the work item to the "done" state.
Adds a new feature, fixes AB#123.                      |	Links and transitions the work item to the "done" state.
Fixes AB#123, AB#124, and AB#126                       |	Links to Azure Boards work items 123, 124, and 126. Transitions only the first item, 123 to the "done" state.
Fixes AB#123, Fixes AB#124, Fixes AB#125               | Links to Azure Boards work items 123, 124, and 126. Transitions all items to the "done" state.
Fixing multiple bugs: issue #123 and user story AB#234 |	Links to GitHub issue 123 and Azure Boards work item 234. No transitions.

---

# Branching strategy

## Pull code from the upstream develop branch and create a new branch:

```
git checkout develop
git pull upstream develop
git checkout –b <my-new-feature>
```

## Work on your feature, stage your changes, and commit changes:

```
git add <file names>
git commit –m “AB#1234 <description of your change>”
```

Make multiple commits if necessary, do not include changes to different features or components in one Pull Request, this increases the difficulty of testing.

Push your branch to a fork of the repository “digital-experience-platform/acsf repository.

```
git push <my repository> <my-new-feature>
```

Ensure all validation test run on push, they should look like:

```
Executing .git/hooks/pre-push...
> validate
> tests:composer:validate
Validating composer.json and composer.lock...
[ExecStack] composer validate --no-check-all --ansi
[ExecStack] Running composer validate --no-check-all --ansi in /Users/ted_slesinski/repos/acsf
./composer.json is valid, but with a few warnings
See https://getcomposer.org/doc/04-schema.md for details on the schema
License "GPL-2.0+" is a deprecated SPDX license identifier, use "GPL-2.0-or-later" instead
The package "behat/mink-selenium2-driver" is pointing to a commit-ref, this is bad practice and can cause unforeseen issues.
[ExecStack] Done in 0.811s
> tests:php:lint
Linting PHP files...
Iterating over fileset files.php.custom.modules...
Iterating over fileset files.php.custom.themes...
> tests:phpcs:sniff:all
[ExecStack] '/Users/ted_slesinski/repos/acsf/vendor/bin/phpcs'
............................................................   60 / 2090 (3%)
............................................................  120 / 2090 (6%)
............................................................  180 / 2090 (9%)
............................................................  240 / 2090 (11%)
............................................................  300 / 2090 (14%)
............................................................  360 / 2090 (17%)
............................................................  420 / 2090 (20%)
............................................................  480 / 2090 (23%)
............................................................  540 / 2090 (26%)
............................................................  600 / 2090 (29%)
............................................................  660 / 2090 (32%)
............................................................  720 / 2090 (34%)
............................................................  780 / 2090 (37%)
............................................................  840 / 2090 (40%)
............................................................  900 / 2090 (43%)
............................................................  960 / 2090 (46%)
............................................................ 1020 / 2090 (49%)
............................................................ 1080 / 2090 (52%)
............................................................ 1140 / 2090 (55%)
............................................................ 1200 / 2090 (57%)
............................................................ 1260 / 2090 (60%)
............................................................ 1320 / 2090 (63%)
............................................................ 1380 / 2090 (66%)
............................................................ 1440 / 2090 (69%)
............................................................ 1500 / 2090 (72%)
............................................................ 1560 / 2090 (75%)
............................................................ 1620 / 2090 (78%)
............................................................ 1680 / 2090 (80%)
............................................................ 1740 / 2090 (83%)
............................................................ 1800 / 2090 (86%)
............................................................ 1860 / 2090 (89%)
............................................................ 1920 / 2090 (92%)
............................................................ 1980 / 2090 (95%)
............................................................ 2040 / 2090 (98%)
..................................................           2090 / 2090 (100%)
```

Open a PR to: digital-experience-platform/acsf:develop

Ensure all CI Tests are passing and assign to a reviewer.

---

# QA

TBD

---

# Releases

Releases should created at:

https://github.mars.com/digital-experience-platform/acsf/releases/new

**Tag version** will be in the format of the approved tagging convention: TBD

Target should be: **master**

Release should have a title and a description including all features of the release.

---

# Project links

* [Repository](https://github.mars.com/digital-experience-platform/acsf)
* [Azure Devops](https://marsdevteam.visualstudio.com/MarsExperiencePlatform)
* [Azure Pipelines](https://marsdevteam.visualstudio.com/MarsExperiencePlatform/_build)
* [Acquia Cloud](https://cloud.acquia.com/app/develop/applications/f9d8b9b4-8a11-4c11-ae1b-06816b04aa57)
* Acquia Site Factory UI:
  * [Live](https://www.marsinc.acsitefactory.com)
  * [Test](https://www.test-marsinc.acsitefactory.com)
  * [Dev](https://www.dev-marsinc.acsitefactory.com)

---

# Resources

Additional [BLT documentation](https://docs.acquia.com/blt/) may be useful. You may also access a list of BLT commands by running this:

```
$ ddev blt
```

Note the following properties of this project:
* Primary development branch: #GIT_PRIMARY_DEV_BRANCH
* Local environment: #LOCAL_DEV_SITE_ALIAS
* Local site URL: #LOCAL_DEV_URL

## Working With a BLT Project

BLT projects are designed to instill software development best practices (including git workflows).

Our BLT Developer documentation includes an [example workflow](https://docs.acquia.com/blt/developer/dev-workflow/).

### Important Configuration Files

BLT uses a number of configuration (`.yml` or `.json`) files to define and customize behaviors. Some examples of these are:

* `blt/blt.yml` (formerly blt/project.yml prior to BLT 9.x)
* `blt/local.blt.yml` (local only specific blt configuration)
* `drush/sites` (contains Drush aliases for this project)
* `composer.json` (includes required components, including Drupal Modules, for this project)
