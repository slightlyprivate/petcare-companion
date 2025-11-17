# Petcare Companion — UI/UX & Branding Guide

Date: 2025-11-16

## Context

This document outlines the foundational UI/UX design principles, branding identity, and frontend
aesthetic system for **Petcare Companion**, a portfolio-ready full-stack application for pet
wellness tracking and social discovery. The current goal is to finalize an MVP with lean, scalable
architecture and presentable polish for employer review, demo, and potential private use later.

## Key Points

### 🎨 Brand Color Palette

| Color Name    | Hex       | Usage                             |
| ------------- | --------- | --------------------------------- |
| Midnight Blue | `#1F2D3D` | Primary brand color, headings, UI |
| Teal Blue     | `#147E7E` | CTA buttons, accent highlights    |
| Soft Aqua     | `#C8F4F9` | Light backgrounds, UI panels      |
| Warm Sand     | `#F6E8D5` | Secondary background/accent       |
| Slate Gray    | `#6B7C93` | Neutral body text, form borders   |
| White Smoke   | `#F9FAFB` | Main background/base surface      |

All color combinations are designed to be **accessible**, **neutral**, and **professional**. No
emojis or cartoonish color variants will be used.

---

### 🎯 Color Semantic Mapping

This table shows how brand colors map to functional UI purposes:

| Semantic Role   | Brand Color Used | Hex       | Purpose                                                        |
| --------------- | ---------------- | --------- | -------------------------------------------------------------- |
| **Primary**     | Midnight Blue    | `#1F2D3D` | Headings, primary buttons, main navigation, UI chrome          |
| **Accent**      | Teal Blue        | `#147E7E` | CTA buttons, interactive elements, focus states, highlights    |
| **Secondary**   | Warm Sand        | `#F6E8D5` | Secondary backgrounds, soft accents, secondary actions         |
| **Muted**       | Soft Aqua        | `#C8F4F9` | Disabled states, placeholders, low-priority UI                 |
| **Foreground**  | Slate Gray       | `#6B7C93` | Body text, borders, neutral UI elements                        |
| **Background**  | White Smoke      | `#F9FAFB` | Main surface, card backgrounds, default page background        |
| **Destructive** | Red              | `#D32F2F` | Errors, delete actions, warnings (non-brand color for urgency) |

---

### 🔤 Typography System

| Type      | Font              | Usage                       |
| --------- | ----------------- | --------------------------- |
| Headings  | Inter (600–800)   | Hero text, section titles   |
| Body      | Inter / Open Sans | All general UI copy         |
| Monospace | JetBrains Mono    | Developer info, diagnostics |

Typography is clean, mobile-friendly, and optimized for accessibility.

---

### 🖼️ Logo & Visual System

**Logo Concept:**

- Minimal, geometric design
- Avoids emoji or cartoon styles
- Suitable for favicon, PWA icon, app nav
- Examples:
  - Abstract pawprint in shield
  - “PC” monogram inside soft hexagon
  - Dog+cat silhouette with heart tail

**Imagery:**

- Flat, abstract illustrations (e.g. undraw.co, Sapiens)
- Optional human-pet silhouette imagery for hero section
- Clean dashboard overlays to suggest utility + warmth

---

## Layout Plan

### 📄 Landing Page

#### Hero Section

**Headline:**

> Your Trusted Digital Companion for Pet Wellness & Care.

**Subheadline:**

> Easily track vaccinations, medications, appointments, and share your pet’s story with the world.

**Primary CTA:**

> [ Get Started — Free for pet lovers. Private by default. ]

#### Feature Highlights (3–4 Card Grid)

1. **Smart Reminders** — Stay ahead of appointments, vaccines, and meds.
2. **Unified Pet Profile** — Centralized records for every pet in your home.
3. **Gift-Driven Support** — Send or receive digital gifts from friends and fans.
4. **Privacy-First** — Built with a secure architecture and clear consent.

#### Footer Call to Action

> Join hundreds of pet parents keeping better care logs and building deeper bonds.

---

### 📱 Auth Pages

- Minimal, mobile-first
- Brand color accent on buttons + logo badge
- Auth flow: Email OTP (no password)
- Copy: “Welcome to Petcare Companion — Please verify your email to continue.”

---

### 🧭 Authenticated Layout Shell

- Left or bottom nav depending on viewport
- Primary views:
  - Dashboard (upcoming care events)
  - Pets
  - Appointments
  - Gifts (activity feed or ledger)
  - Account

---

### 🧱 Future Brand Values

- **Privacy-respecting by design** (no 3rd-party trackers)
- **Self-hostable or local-first** (ideal for React Native expansion)
- **Modular API** (easy clinic/shelter integrations)
- **Shared care accounts** (families, roommates, vet assistants)

## Next Steps

1. Finalize logo (vector + favicon) using selected direction
2. Begin implementing the layout shell and routing stubs
3. Design mobile-first landing and auth pages with Tailwind
4. Reuse color system and typography in every component scaffold

## References

- <https://fonts.google.com/specimen/Inter>
