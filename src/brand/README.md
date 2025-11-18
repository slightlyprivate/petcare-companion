# Brand Assets

This directory contains shared brand assets for the PetCare Companion application, accessible by
both the Laravel backend and React frontend.

## Structure

```
brand/
├── logo/
│   ├── petcare-logo.svg          # Main logo
│   ├── petcare-logo-dark.svg     # Dark mode logo
│   ├── petcare-mark.svg          # Pawprint icon only
│   ├── app-icon-1024.png         # App icon for stores
│   └── favicon-32.png            # Favicon
├── illustrations/
│   ├── hero.jpg                  # Hero image
│   ├── pets-group.svg            # Pets illustration
│   └── patterns.svg              # Background patterns
├── icons/
│   ├── pawprint-outline.svg      # Outline pawprint
│   └── pawprint-filled.svg       # Filled pawprint
└── README.md                     # This file
```

## Usage

### Laravel (Backend)

Assets are symlinked to `public/brand/` and served by Laravel at `/brand/...`

Example in Blade templates:

```blade
<img src="/brand/logo/petcare-logo.svg" alt="PetCare Logo">
```

### React (Frontend)

Load assets using absolute URLs:

```jsx
<img src="/brand/logo/petcare-logo.svg" alt="PetCare Logo" />
```

## Guidelines

- Keep assets optimized for web (SVG preferred for vectors, compressed images)
- Use consistent naming conventions
- Update this README when adding new assets
- Assets are version-controlled for consistency across environments
