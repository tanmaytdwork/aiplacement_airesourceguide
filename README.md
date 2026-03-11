# AI Resource Guide

## About This Project

**AI Resource Guide** is a Moodle AI placement plugin that enhances Page activities with contextual learning references. It integrates with Moodle's AI subsystem (introduced in Moodle 4.5) to analyze course content and surface relevant external resources for students.

**What it does:** Adds a "Find References" button to Page activities. When clicked, it uses Moodle's AI subsystem to analyze the page content, identify 3–5 key concepts a student might need help with, and return curated external reference links (Google Scholar, YouTube, Wikipedia, DevDocs, etc.) in a slide-in drawer.

The plugin covers the full stack of a production Moodle plugin: placement class, web service with security checks, caching layer, AMD frontend module, Mustache templates, admin settings, capability definitions, and GDPR compliance.

---

## Technical Overview

### Moodle AI Subsystem (Placement → Action → Provider)
Moodle 4.5 introduced a three-tier AI architecture. This plugin implements the **Placement** tier correctly:

- Extends `\core_ai\placement` and declares support for the `generate_text` action.
- Uses `\core_ai\manager::process_action()` to dispatch to whichever provider the site administrator has configured (OpenAI, Azure AI, Ollama, etc.).
- Does **not** talk to any AI API directly — it goes through the subsystem, making the plugin provider-agnostic.
- Registers via `admin_settingspage_provider` so it appears in Site Administration → AI alongside core placements.

```
Student clicks [Find References]
        │
        ▼
hook_listener.php  ← before_footer_html_generation hook
        │             (only fires on Page activities, capability-checked)
        ▼
guide.js (AMD)  ← checks AI user policy → opens drawer → calls web service
        │
        ▼
get_references.php (external API)  ← validates params, context, capability
        │
        ▼
utils.php  ← reads page content → builds prompt → calls generate_text action
        │
        ▼
AI Subsystem Manager  ← routes to site's configured provider
        │
        ▼
utils.php  ← parses JSON response → maps to real search URLs → caches result

```

## Key Technical Decisions

### 1. Reusing `generate_text` instead of a custom action
Rather than creating a new action type, the plugin reuses `core_ai\aiactions\generate_text`. The AI intelligence is entirely in the prompt and response parsing. This was a deliberate decision — it means the plugin works with every existing and future provider without any extra configuration.

### 2. No AI-hallucinated URLs
The AI is only asked to extract concept names, search queries, and suggest source *types* (e.g. `"academic"`, `"video"`). The actual URLs are constructed programmatically using real search engine patterns with URL-encoded queries. This eliminates the risk of the AI returning broken or fabricated links.

```php
// Source URL patterns — {query} is replaced at runtime, never from AI output.
'academic' => 'https://scholar.google.com/scholar?q={query}',
'video'    => 'https://www.youtube.com/results?search_query={query}',
```

### 3. Hook-based injection
Instead of modifying any core files, the plugin uses Moodle's hook system (`before_footer_html_generation`) to inject its JavaScript. The hook listener checks context level, verifies the module is a Page activity, and confirms the user has the capability — all before loading any assets.

### 4. Caching to control AI costs
AI API calls are expensive. The plugin caches results per course module ID for 24 hours. If ten students view the same page on the same day, only one AI call is made.

### 5. AI User Policy compliance
The frontend checks `core_ai_get_policy_status` before making any AI call. If the user hasn't accepted the policy, a confirmation modal is shown. This is required by Moodle's AI subsystem and mirrors what core placements do.


---

## Requirements

- Moodle 4.5 or later
- At least one AI Provider configured in Site Administration → AI
- The `generate_text` action enabled for that provider

## Installation

1. Copy the `airesourceguide` folder to `ai/placement/` in your Moodle root.
2. Visit **Site Administration → Notifications** to run the upgrade.
3. Go to **Site Administration → AI** to confirm the plugin appears in the placements list.
4. Configure which reference sources to enable under the plugin settings.

---

## File Structure

```
ai/placement/airesourceguide/
├── classes/
│   ├── placement.php            # AI placement class
│   ├── hook_listener.php        # Page hook → AMD injection
│   ├── utils.php                # Core logic: prompts, AI calls, caching
│   ├── external/
│   │   └── get_references.php   # Web service endpoint
│   └── privacy/
│       └── provider.php         
├── db/
│   ├── install.xml              # No custom tables
│   ├── access.php               # Capability definitions
│   ├── services.php             # Web service registration
│   ├── hooks.php                # Hook callback registration
│   └── caches.php               # Cache definition 
├── amd/src/
│   └── guide.js                 # Button, drawer, AJAX, policy check
├── templates/
│   ├── drawer.mustache         
│   └── results.mustache         
├── lang/en/
│   └── aiplacement_airesourceguide.php
├── styles.css
├── settings.php                 # Admin: enable/disable source types
└── version.php                 
```

---
