# Icon System

This project uses a centralized icon system based on **[lucide-react](https://lucide.dev/)**, a
popular open-source icon library with over 1,000 icons.

## Why Lucide React?

- ✅ **Type-safe**: Full TypeScript support with proper type definitions
- ✅ **Tree-shakeable**: Only imports icons you actually use
- ✅ **Consistent**: All icons have the same design language
- ✅ **Customizable**: Size, color, and stroke-width can be adjusted
- ✅ **React components**: Native React components, not inline SVGs
- ✅ **Accessible**: Proper ARIA attributes and semantic HTML
- ✅ **Lightweight**: ~1KB per icon when bundled

## Usage

### Basic Usage

```tsx
import { Icon } from '../components/icons';

// Simple icon
<Icon name="check" />

// With custom size
<Icon name="pencil" size={20} />

// With custom styling
<Icon name="trash" size={16} className="text-red-600" />

// With custom stroke width
<Icon name="user" size={24} strokeWidth={1.5} />
```

### Available Icons

See `/src/ui/src/components/icons/Icon.tsx` for the complete registry of available icons.

Common icons include:

- **Actions**: `check`, `pencil`, `trash`, `plus`, `close`, `copy`
- **Navigation**: `chevronDown`, `chevronUp`
- **Time**: `calendar`, `clock`
- **Users**: `user`, `users`
- **Status**: `spinner`, `alertCircle`, `checkCircle`, `xCircle`, `info`
- **Activities**: `feeding`, `walk`, `play`, `medication`, `grooming`, `vet`, `vaccination`, `other`

### Adding New Icons

1. Import the icon from `lucide-react` in `/src/ui/src/components/icons/Icon.tsx`:

```tsx
import { NewIcon } from 'lucide-react';
```

Add it to the `icons` registry:

```tsx
export const icons = {
  // ... existing icons
  newIcon: NewIcon,
} as const;
```

Use it anywhere in your components:

```tsx
<Icon name="newIcon" size={20} />
```

## Benefits Over Inline SVGs

**Before** (inline SVG):

```tsx
<svg className="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
  <path
    strokeLinecap="round"
    strokeLinejoin="round"
    strokeWidth={2}
    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
  />
</svg>
```

**After** (Icon component):

```tsx
<Icon name="pencil" size={16} />
```

### Advantages

- **Cleaner code**: 1 line instead of 10+
- **Type safety**: TypeScript catches invalid icon names
- **Consistency**: All icons follow the same patterns
- **Maintainability**: Update icons in one place
- **Performance**: Tree-shaking eliminates unused icons
- **Accessibility**: Built-in ARIA attributes

## Icon Component Wrappers

For domain-specific icon usage (like activity types), we maintain wrapper components:

```tsx
// /src/ui/src/lib/activityIcons.tsx
export function FeedingIcon({ className }: IconProps) {
  return <Icon name="feeding" className={className} size={20} />;
}
```

This allows easy icon swapping without changing all usage sites.

## Best Practices

1. **Use semantic names**: Choose icon names that describe their purpose
2. **Consistent sizing**: Use standard sizes (16, 20, 24) across the app
3. **Color through className**: Use Tailwind classes for colors
4. **Stroke width**: Keep default (2) unless special emphasis needed
5. **Accessibility**: Icons should complement text, not replace it

## Resources

- [Lucide Icon Library](https://lucide.dev/) - Browse all available icons
- [Lucide React Docs](https://lucide.dev/guide/packages/lucide-react) - Official documentation
