# Contributing to All in One SEO Pack

So you'd like to contribute to an open source project? You're awesome!

- [Reporting bugs](#reporting-bugs)
- [Feature ideas](#feature-ideas)
- [Contributing code](#contributing-code)
- [Translating](#translating)


## Reporting bugs

1. Support issues (white screen of death, plugin/theme conflict, post meta or titles not showing up, etc.) should go to the [**support forums**](http://semperplugins.com/support/). Make sure you're reporting a true AIOSEOP issue here. First do some [basic debugging](http://semperplugins.com/faqs/how-to-troubleshoot-issues-with-our-plugins/).
2. [**Search** the issues](https://github.com/semperfiwebdesign/all-in-one-seo-pack/issues) first.
3. [Open a new issue](https://github.com/semperfiwebdesign/all-in-one-seo-pack/issues/new).

What makes the issue really helpful:

- You articulate the problem clearly and provide **steps to reproduce** the problem.
- **Screenshots or GIFs** are appreciated.


## Feature ideas

Ideas are great. All in One SEO Pack needs them. There are so many difficult problems still to solve, and so many opportunities to make the project better. :bulb: :bulb: :bulb:

[Submit a new feature request](https://github.com/semperfiwebdesign/all-in-one-seo-pack/issues/new) and start a discussion.


## Contributing code

Generally:

1. Open a new issue / pick an existing one
2. Fork the repo, create a branch, commit to it 
3. Push the branch, open a pull request
4. The core team will review it and work with you if necessary
5. Someone from the core team will merge the PR
6. :tada:

Smaller changes like updating README's etc. don't need to use the full workflow, a direct PR or sometimes even a commit into `master` is fine. However, most code changes undergo the suggested workflow which is described in more detail [below](#development-workflow).

The following discusses some of the important details if you want to contribute.

### Core values

- **We care about user / dev experience**. Everything that is outward-facing, be it a user interface, developer API or a file format, must be carefully designed for usability and usefulness. We invest our energy to save it for the others.
- **We care about code quality**. Bad code is a liability, not an asset. We value tests, review each other's code and try to make it good and clean.
- **We try to be pragmatic**. While we care about quality, the main thing for All in One SEO Pack and its users is to move forward. We're always looking for the right balance.


### Our development process

**Major versions** (2.0, 2.1 etc.) are released every few months. Each major version has a [corresponding milestone](https://github.com/semperfiwebdesign/all-in-one-seo-pack/milestones/) and issues are assigned to it by the core team. Issues not assigned to any milestone are in a backlog – we want to do them one day but there's no immediate plans yet.

**Issues** are the most important tool to plan and manage almost everything around VersionPress:

- We create them for new features, bugs, improvements or even larger things like planning documents. **We strongly prefer issues over wiki** or other documents as they are actionable and time-framed.
- [This set of **labels**](https://github.com/semperfiwebdesign/all-in-one-seo-pack/wiki/Issues#labels) is used to categorize issues.
- Issues go through **four states**: 'open', 'in progress', 'in review' and 'closed'. There's an [**overv.io board**](https://overv.io/workspace/JanVoracek/cautious-tarsier/) board to visualize that. Also, overv.io helps us set priorities – tickets higher up will be worked on first.

Regarding **branches**, the current release being worked on is **`development`**. It is hence inherently unsafe, even though we do our best to keep it in a good shape. **`Master`** is typically relatively stable.


### Development workflow

For small / "safe" changes like updating a README or other Markdown files, quick pull request or even commit into `master` is acceptable. However, for most new code, we use the [GitHub flow](https://guides.github.com/introduction/flow/):

![GitHub Flow](https://guides.github.com/activities/hello-world/branching.png)

Here are the details:


1. When you start working on an issue, **move it to the 'in progress' state** (either visually on the [overv.io board](https://overv.io/workspace/JanVoracek/cautious-tarsier/) or by assigning the `in progress` label to the issue) and **create a new feature branch** for it. Name it `<issue number>-<short description>`, e.g., `123-row-filtering`.

    - **Every feature branch should branch off of master**, not another feature branch, even if it depends on it. For dependent feature branches, simply merge between them. This is mainly because when you're going to open a PR for it, you will need to select the target branch (GitHub doesn't let you to change this later) and `master` is the only sensible choice there.
    
2. **Commit to this branch**. We appreciate good commits, here are some tips:

    - **Keep commits small and focused**. There are many articles on version control best practices, e.g., [this one](http://www.git-tower.com/learn/git/ebook/command-line/appendix/best-practices) is good. To sum it up, commit small logical changes, prefer smaller commits over large ones and keep project in a workable state at all times.
    - **Write good commit messages**. We don't have strict rules like [this](http://chris.beams.io/posts/git-commit/), e.g., we don't enforce short subject lines. The main thing for us is that the commit messages are *useful*. Do they make it clear what happened in a commit? Do they reference related commits, if applicable? Good.
        - We most commonly use past tense ("Added tests") or present tense describing the new situation ("IniSerializer now has tests") but we're not religious about it.
    - **Link to an issue from the commit message**. Most of the commit messages look like this:
    
        ```
        [#123] Implemented xyz
        ```
        
        It means that the commit belongs to issue `#123`. It makes looking up issues from commits easier.   


3. When ready, push the branch, **open a pull request** for it and **move the issue to the 'in review' state** (again, either visually in [overv.io](https://overv.io/workspace/JanVoracek/cautious-tarsier/) or by removing the `in progress` label and adding the `in review` one). You can open a PR early to gather feedback, no worries, you can always add commits to it later. The branch can be push-forced if necessary, it is a "sandbox" to make it great.

    This is an example of a good pull request: [versionpress/versionpress#744](https://github.com/versionpress/versionpress/pull/744). The body usually contains something like:
    
        Resolves #123.
        
        Some notes on the implementation here if it's not obvious from the code
        or the list of commits.
        
        Reviewers:
        
        - [ ] @JanVoracek 
        - [ ] @borekb 
    
    It will be pre-filled for you automatically via GitHub templates, just with a different reviewer (`@versionpress/core-devs` will be there by default, someone from the core team will update it to the actual list of people).
    
4. **Core team reviews the PR**. Expect feedback – it is uncommon to receive none – and be open to it. The team will happily work with you to make the code contribution great.

    All checkboxes checked means that the PR is OK to merge.
    
    > This is an important nuance because the checkbox can have two meanings: "PR is OK to merge" or "I am done with the review (regardless of whether I still see issues with the code or not)". The former is useful for the one who will eventually perform the merge, the latter is more convenient for a reviewer. We use the first meaning which means that I, as a reviewer, will only check the checkbox after I reported some issues with the code **and they have been fixed**.   
    
5. Someone from the core team **merges the pull request**, issue is closed and the branch can be deleted.

A couple of notes:

- As noted above, small / safe changes don't need to undergo this whole process. For example, Markdown files can be **committed directly into `master`** if the changes don't need to be reviewed.
- We used to use **rebasing** in the past – you can still see that in commits before April 2015 – but left it in favor of merging which is much more natural on GitHub. Plus, rebases [have their own issues](http://geekblog.oneandoneis2.org/index.php/2013/04/30/please-stay-away-from-rebase).
- **Issues vs. pull requests**: most of the new improvements and features start as issues as they are quick to create and don't require a Git branch. Then there's usually a single PR against the issue (sometimes more but that's relatively rare). However, issues and pull requests are almost the same thing on GitHub and it's not a problem to start something (possibly simpler) directly as a PR.


### Style guides

All in Code should follow the [WordPress Coding Standards](https://make.wordpress.org/core/handbook/best-practices/coding-standards/) and [WordPress Documentation Standards](https://make.wordpress.org/core/handbook/best-practices/inline-documentation-standards/).

#### PHP style guide

[WordPress PHP Coding Standards](https://make.wordpress.org/core/handbook/best-practices/coding-standards/php/)

[WordPress PHP Documentation Standards](https://make.wordpress.org/core/handbook/best-practices/inline-documentation-standards/php/)

There are tools out there for apps like Netbeans and PHPStorm to apply WordPress Coding Standards.


#### JavaScript style guide

[WordPress Javascript Coding Standards](https://make.wordpress.org/core/handbook/best-practices/coding-standards/javascript/)

[WordPress Javascript Documentation](https://make.wordpress.org/core/handbook/best-practices/inline-documentation-standards/javascript/)



## Translating

We would love your help translating the project into your language. [Translations](https://translate.wordpress.org/projects/wp-plugins/all-in-one-seo-pack)


[How to Contribute Translations](https://github.com/semperfiwebdesign/all-in-one-seo-pack/wiki/Translating-into-your-language)

[Translations FAQ](https://github.com/semperfiwebdesign/all-in-one-seo-pack/wiki/Translations-FAQ)


---

Other ideas of how to contribute? [Tell us](http://semperplugins.com/contact). 
