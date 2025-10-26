# ![logo.svg](public/logo.svg) Cloud


Build and deploy modular Laravel applications with ease.

### Glugox Modular Ecosystem

## Introduction

> **Goal:** Design a Laravel‑first modular system that turns small JSON specs into fully installable Composer packages (“modules”), orchestrated inside a root Laravel app. We will build this *incrementally* using TDD and keep documentation in lock‑step with implementation.

---

## 0) Audience & Scope

**Audience:** Laravel developers (package authors and app integrators).
**Scope (current doc):** Concepts, user flows, constraints, success criteria, and TDD roadmap. **No code yet.**

---

## 1) Core Concepts

**Module**
A Composer package that ships app features (models, migrations, seeders, HTTP/API, Vue pages, assets). Each module is defined by one JSON file that conforms to the shared JSON Schema.

**Spec**
A JSON configuration that declares `app` metadata and `entities` with `fields`, `relations`, `filters`, `actions`, and optional `settings`/`fakerMappings`.

**Orchestrator**
A root‑app package that discovers specs, builds modules from them, and manages their lifecycle (install, update, remove). (Planned integration with `glugox/orchestrator`).

**Module Generator**
A build tool that transforms JSON specs into module source code (planned `glugox/magic`).

**Module Runtime**
A shared library that exposes base abstractions and contracts used by all modules at runtime (planned `glugox/module`).

---

## 2) End‑User Happy Path (Developer Flow)

> This is the **experience we are designing towards**. We will make it real across phases.

1. **Install Laravel (latest)**

   ```bash
   laravel new demo-app && cd demo-app
   ```

2. **Drop JSON specs** into `specs/modules/` (one JSON per module).
   Example files: `specs/modules/Users.json`, `specs/modules/Projects.json` …

3. **Build modules** (via Orchestrator, later):

   ```bash
   php artisan modules:build  # discovers specs, validates, generates code pkgs
   ```

4. **Install modules** into the app:

   ```bash
   composer require company/users-module:dev-build
   composer require company/projects-module:dev-build
   ```

5. **Run install**:

   ```bash
   php artisan modules:install  # registers modules, publishes migrations, runs migrations, seeders
   ```

6. **Use the features** (routes, API, UI) provided by the modules.

> For now we will document and design. In later phases we’ll make each step pass with tests.

---

## 3) JSON Schema (Authoring Rules)

We adopt the schema the team provided (Draft‑07). Every module spec **must**:

* include `app.name`
* define `entities[]`
* use only allowed `field.type` and `relation.type` enums
* if `actions[].type == "command"`, include `actions[].command`

**Validation Strategy (planned):**

* Use a strict JSON Schema validator during build (`modules:build`).
* Fail fast on unknown properties unless explicitly allowed via `settings`.

**Naming Guidelines:**

* `app.name` should be unique per module.
* `entities[].name` uses PascalCase, singular (e.g., `OrderItem`).
* `fields[].name` uses snake_case.
* `relations[].relatedEntityName` references **entity names** within the same module *or* (later) cross‑module.

---

## 4) Directory Layout (Root App)

```
/ (Laravel app root)
├─ specs/
│  └─ modules/
│     ├─ Users.json
│     ├─ Projects.json
│     └─ ...
├─ modules/               # (optional) local path for generated pkgs in dev
│  └─ company/
│     └─ users-module/
│        ├─ src/
│        ├─ database/migrations/
│        ├─ database/seeders/
│        ├─ routes/
│        ├─ resources/ (views/js/css)
│        └─ composer.json
└─ composer.json
```

> In CI we may publish generated packages to an artifact registry or install via `path` repositories in Composer for fast iteration.

---

## 5) Module Lifecycle (Planned)

1. **Discover** specs in `specs/modules/*.json`.
2. **Validate** each spec against JSON Schema; produce a readable report.
3. **Generate** a module skeleton: package metadata, service provider, config, migrations, models, factories, seeders, routes, controllers, resources.
4. **Install** the module into the app (`composer require` or `repositories: path` + `require`), register service provider, publish migrations, run migrations, run seeders
5**Update** (re‑generate safely) or **Remove** (uninstall) as needed.

**Design Principles:**

* **Idempotent builds** (re‑running `modules:build` is safe).
* **Declarative**: JSON is the source of truth.
* **Composable**: many small modules > one giant module, communicating via events (future).
* **Observable**: logs, build summaries, and diff reports.

---

## 6) Minimal Viable Feature Set (MVF)

> The smallest slice that proves the architecture and is testable.

* Spec discovery & validation (JSON Schema)
* Basic codegen for: migrations, Eloquent models, simple controllers, routes(api)
* Field types: `id`, `string`, `text`, `boolean`, `integer`, `decimal`, `date`, `dateTime`, `enum`, `json`
* Relations: `belongsTo`, `hasMany`
* Filters scaffold (API contract only; no runtime DSL yet)
* Actions scaffold (recorded as metadata; `command` type stubbed)

---

## 7) TDD Roadmap (Phased)

> We will implement **one thin slice per phase**, keeping tests/documentation green.

### Phase 0 — Docs Only (this document)

* ✅ Define vocabulary, goals, UX, constraints.
* ✅ Agree on MVF and acceptance criteria.

### Phase 1 — Spec Validation

**Objective:** Given *.json in `specs/modules`, validate against schema and emit a report.

* Unit: JSON validator accepts valid specs and rejects invalid ones with clear messages.
* CLI: `php artisan modules:validate` prints a table + non‑zero exit code on failure.
* Artifacts: `storage/app/modules/validation.json` (machine‑readable).

**Acceptance Criteria:**

* Invalid enum value in a field → fails with pointing path.
* Missing required key (e.g., `app.seedCount`) → fails with hint to fix.

### Phase 2 — Codegen: Entities → Migrations/Models

**Objective:** Generate migrations/models for supported field types and basic relations.

* Unit: field type → correct column/migration; relation → correct FK & inverse.
* Integration: applying migrations succeeds; models load/save; mass assignment guarded.
* CLI: `php artisan modules:build --dry` (prints plan) and `php artisan modules:build` (writes files to a temp module dir).

**Acceptance Criteria:**

* Running `migrate` after build creates expected tables & FKs.
* Re‑running build is idempotent (no duplicate migrations unless entity changed).

### Phase 3 — Package Assembly & Autodiscovery

**Objective:** Wrap generated code as a Composer package with a service provider.

* Unit: generated `composer.json` has PSR‑4, `extra.laravel.providers`.
* Integration: `composer config repositories.* path`, `composer require` works.
* CLI: `php artisan modules:install` installs latest builds into app.

**Acceptance Criteria:**

* Service provider boots routes and publishes migrations when applicable.
* Module can be enabled/disabled without touching app core.

### Phase 4 — Seeders & Faker Mappings

**Objective:** Respect `app.seedEnabled/seedCount` and `fakerMappings`.

* Unit: faker map key → entity field mapping.
* Integration: `db:seed` inserts realistic records; counts respect `seedCount`.

**Acceptance Criteria:**

* When `seedEnabled=false`, no module seeders run by default.

### Phase 5 — Filters & Actions (API Contract)

**Objective:** Materialize filters & actions as metadata and simple endpoints.

* Unit: filter JSON → stored contract; action(type=command) → invokable stub.
* Integration: `/api/{entity}` accepts query params aligned with filters spec.

**Acceptance Criteria:**

* Unknown filter types rejected at validation time.
* Action with `type=command` without `command` → validation failure.

> Future Phases: UI Scaffolding, Cross‑Module Relations, Events, Queued Actions, Caching, Versioning.

---

## 8) Non‑Goals (for now)

* Cross‑module relations (will design later with namespacing and import maps).
* Complex policy generation/authorization (stub allow‑all for MVF).
* Full‑featured UI scaffolding (API‑first in MVF; UI later).
* Event bus and inter‑module workflows (post‑MVF).

---

## 9) Constraints & Design Choices

* **Laravel 11+** baseline (latest at project start).
* **Composer packages per module** to enable distribution and versioning.
* **PSR‑4** namespaces derived from spec (convention: `Vendor\{AppName}`; configurable later).
* **Autodiscovery** via `extra.laravel.providers` (no manual app edits).
* **Idempotency**: Build steps compute deterministic outputs; safe overwrites with file‑delta reports.

---

## 10) Acceptance Criteria (System Level)

* A developer can:

    1. place 2–5 valid specs into `specs/modules/`
    2. run `modules:validate` → all pass
    3. run `modules:build` → generated module packages
    4. run `modules:install` → modules autoload and register
    5. `migrate` + (optionally) `db:seed` → database tables & sample data exist.

* Re‑running the above is safe and produces the same result unless specs changed.

---

## 11) Test Matrix (Initial)

| Area       | Case                                   | Expectation                                                                       |
| ---------- |----------------------------------------| --------------------------------------------------------------------------------- |
| Validation | Missing `app.name`                     | Fails with path and hint                                                          |
| Validation | Unknown `field.type`                   | Fails with enum suggestion                                                        |
| Validation | Action `type=command` but no `command` | Fails with required key                                                           |
| Codegen    | `enum` field                           | Adds check constraint / casts as string enum in model (MVP: string column + cast) |
| Relations  | `belongsTo` + `hasMany`                | FK + inverse relation methods generated                                           |
| Seeds      | `seedEnabled=false`                    | Seeders not hooked                                                                |
| Seeds      | `seedCount > 0`                        | Factory creates count per entity respecting simple dependencies                   |

---

## 12) Observability & DX

* **Logs:** `storage/logs/modules.log` with build timing, decisions, and warnings.
* **Reports:** JSON summaries of validation/build stored under `storage/app/modules/`.
* **Dry‑Run:** `--dry` flag prints plan without writing files.
* **Diffs:** Later, use file‑delta to preview changes before overwrite.

---

## 13) Security & Safety

* Generated code uses guarded fillable defaults; hidden for `password` fields.
* No shell commands executed from spec at MVF (actions are stubs only).
* Path repositories limited to app workspace by default.

---

## 14) Versioning & Compatibility

* Spec files include no explicit `schemaVersion` yet; we assume **v1**.
* When we introduce `schemaVersion`, builder will support migration rules & deprecations.

---

## 15) Open Questions (to resolve before code)

1. What is the canonical vendor namespace for generated modules (e.g., `Company` or `Glugox`)?
2. Do we store generated modules under `/modules` (path repo) or build into a temp dir and publish to a local registry/VC?
3. How do we handle collisions when two modules export an `Entity` with the same name?
4. Minimum Laravel features we rely on (Scout? Sanctum?) for MVF.

---

## 16) Next Step (Actionable)

* **Proceed to Phase 1: Spec Validation.**
  We will:

    * Choose a PHP JSON Schema validator.
    * Write failing tests for the validation CLI.
    * Implement the minimal validator wrapper.
    * Deliver: Green tests + `modules:validate` + human & JSON reports.

> When you’re ready, say: **“Start Phase 1 — Spec Validation.”** We’ll create the test list and scaffold the CLI, still following TDD.
