import { KeyboardEvent } from 'react';

interface Params {
  activeIndex: number;
  setActiveIndex: (i: number) => void;
  enabledLength: number;
  focusTab: (i: number) => void;
  orientation?: 'horizontal' | 'vertical';
}

/**
 * useTabsKeyboardNavigation Hook
 *
 * Provides keyboard navigation for tab components, allowing users to navigate
 * between tabs using arrow keys, Home/End keys, and activate tabs with Enter/Space.
 */
export function useTabsKeyboardNavigation({
  activeIndex,
  setActiveIndex,
  enabledLength,
  focusTab,
  orientation = 'horizontal',
}: Params) {
  const onKeyDown = (e: KeyboardEvent<HTMLDivElement>) => {
    const lastIndex = enabledLength - 1;
    let nextIndex: number | null = null;

    switch (e.key) {
      case 'ArrowRight':
        if (orientation === 'horizontal') {
          nextIndex = activeIndex === lastIndex ? 0 : activeIndex + 1;
        }
        break;
      case 'ArrowLeft':
        if (orientation === 'horizontal') {
          nextIndex = activeIndex === 0 ? lastIndex : activeIndex - 1;
        }
        break;
      case 'ArrowDown':
        if (orientation === 'vertical') {
          nextIndex = activeIndex === lastIndex ? 0 : activeIndex + 1;
        }
        break;
      case 'ArrowUp':
        if (orientation === 'vertical') {
          nextIndex = activeIndex === 0 ? lastIndex : activeIndex - 1;
        }
        break;
      case 'Home':
        nextIndex = 0;
        break;
      case 'End':
        nextIndex = lastIndex;
        break;
      case 'Enter':
      case ' ': // Space
        setActiveIndex(activeIndex);
        return;
      default:
        return;
    }

    if (nextIndex !== null) {
      e.preventDefault();
      focusTab(nextIndex);
      setActiveIndex(nextIndex);
    }
  };

  return { onKeyDown };
}
