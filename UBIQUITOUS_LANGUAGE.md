# Ubiquitous Language

## Website content

| Term | Definition | Aliases to avoid |
| --- | --- | --- |
| **Article** | A Markdown blog post authored in German, covering a software architecture topic | Blog post, post, entry |
| **Project** | A portfolio item displayed on the homepage, defined in `public/content/projects.json` | Work, case study |
| **Frontmatter** | The YAML metadata block at the top of an **Article** file (title, description, date, tags, image) | Metadata, header, YAML block |
| **Tag** | A label attached to an **Article** via **Frontmatter** for categorization | Category, label |
| **Layout** | The shared PHP page template in `public/templates/layout.php` that wraps every page's HTML shell | Page template, wrapper, shell |
| **Vendor** | A third-party library manually copied into the `public/vendor/` directory — no package manager involved | Dependency, package, library |

## Handbook sync workflow

| Term | Definition | Aliases to avoid |
| --- | --- | --- |
| **Handbook** | The personal reference knowledge base that defines canonical patterns for projects | Guide, docs, wiki |
| **Target project** | The project being audited against the **Handbook** | Repo, codebase, project |
| **Handbook sync** | The process of comparing a **Target project** against the **Handbook** to identify and apply improvements | Audit, review, alignment check |
| **Finding** | A single discrepancy identified during a **Handbook sync**, classified as Missing, Outdated, Divergent, or Aligned | Issue, recommendation, gap |
| **Handbook area** | A specific guide or template in the **Handbook** that applies to the **Target project**'s stack | Section, reference, handbook file |

## Deployment

| Term | Definition | Aliases to avoid |
| --- | --- | --- |
| **Deploy** | The act of syncing project files to the production server via `rsync` over SSH, triggered on push to `main` | Push, publish, release, upload |
| **SSH key** | The asymmetric key pair used to authenticate the CI runner with the production server during a **Deploy** | Password, FTP credentials |

## Relationships

- An **Article** has exactly one set of **Frontmatter** and zero or more **Tags**
- A **Project** is independent of **Articles** — they share only the **Layout**
- A **Finding** belongs to exactly one **Handbook area**
- A **Handbook sync** produces one or more **Findings**
- A **Deploy** is triggered automatically; it is not part of a **Handbook sync**

## Example dialogue

> **Dev:** "I added a new **Article** about CQRS — does it need anything special beyond the **Frontmatter**?"
> **Domain expert:** "No — just a valid **Frontmatter** block with `title`, `date`, and at least one **Tag`. The **Layout** handles everything else."
> **Dev:** "During the **Handbook sync** the CI workflow showed up as a **Finding** — specifically Outdated. What does that mean?"
> **Domain expert:** "It means the **Target project** has the right pattern, but it's behind the **Handbook** — like using `checkout@v4` when the **Handbook area** references `@v5`. Not broken, just stale."
> **Dev:** "And a Divergent **Finding**?"
> **Domain expert:** "That's when the **Target project** consciously does something differently — like having no Makefile because the project rules forbid build tools. We flag it, but we don't auto-fix it."

## Flagged ambiguities

- **"FTP" vs SSH** — The GitHub Actions secrets (`FTP_USER`, `FTP_HOST`) are named as if the transport is FTP, but the actual **Deploy** mechanism is `rsync` over SSH. `FTP_PASSWORD` was replaced with `SSH_PRIVATE_KEY` in this session. `FTP_USER` and `FTP_HOST` remain misleadingly named — consider renaming to `SSH_USER` and `SSH_HOST`.
- **"template"** — used in two unrelated senses: (1) `public/templates/layout.php`, the PHP page **Layout**; (2) `handbook/templates/`, the **Handbook**'s reference files. These are entirely different concepts. Use **Layout** for (1) and **Handbook template** or **handbook area** for (2).
- **"vendor"** — in standard PHP/Composer projects, `vendor/` means Composer-managed packages. Here it means manually copied libraries with no package manager involved. The distinction is material: nothing in `public/vendor/` should ever be auto-updated or gitignored.
