# UI (Account + Billing)

This workspace powers the administrative/account experience for PetCare Companion:

- Account identity, authentication options, and household security
- Billing + Stripe portal, subscription state, and payment history
- Household + pet profile settings, notification preferences, and compliance workflows

## Getting Started

```bash
cd src/ui
npm install
npm run dev -- --host
```

The dev server binds to port `5174` to avoid conflicts with the PetCare PWA (5173). Point
`VITE_API_PROXY_TARGET` at `http://web` when running inside Docker Compose so routes proxy directly
to Laravel.

## Project Conventions

- Keep components focused on administrative settings; never ship caregiving flows from this surface.
- Share primitives (auth clients, design tokens) via packages or `/src/lib` where appropriate.
- Use the PWA in `/src/pwa` for daily caregiver workflows.
