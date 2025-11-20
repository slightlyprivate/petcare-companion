import React, { useRef, useState, useId } from 'react';
import { useTabsKeyboardNavigation } from './useTabsKeyboardNavigation';

export interface TabDefinition {
  id: string;
  label: string;
  content: React.ReactNode;
  disabled?: boolean;
}

interface TabsProps {
  tabs: TabDefinition[];
  initialTabId?: string;
  className?: string;
  listClassName?: string;
  tabClassName?: string;
  panelClassName?: string;
}

// Accessible tabs component with keyboard navigation (arrow left/right, home/end)
export default function Tabs({
  tabs,
  initialTabId,
  className,
  listClassName,
  tabClassName,
  panelClassName,
}: TabsProps) {
  const enabledTabs = tabs.filter((t) => !t.disabled);
  const initialIndex = initialTabId ? enabledTabs.findIndex((t) => t.id === initialTabId) : 0;
  const [activeIndex, setActiveIndex] = useState(Math.max(0, initialIndex));
  const baseId = useId();
  const tabRefs = useRef<(HTMLButtonElement | null)[]>([]);

  const focusTab = (index: number) => {
    const ref = tabRefs.current[index];
    if (ref) ref.focus();
  };

  const activateTab = (index: number) => {
    setActiveIndex(index);
  };

  const { onKeyDown } = useTabsKeyboardNavigation({
    activeIndex,
    setActiveIndex: setActiveIndex,
    enabledLength: enabledTabs.length,
    focusTab,
  });

  return (
    <div className={className}>
      <div
        role="tablist"
        aria-label="Sections"
        className={listClassName || 'flex gap-2 border-b mb-4'}
        onKeyDown={onKeyDown}
      >
        {enabledTabs.map((tab, i) => {
          const selected = i === activeIndex;
          return (
            <button
              key={tab.id}
              ref={(el) => (tabRefs.current[i] = el)}
              role="tab"
              id={`${baseId}-tab-${tab.id}`}
              aria-selected={selected}
              aria-controls={`${baseId}-panel-${tab.id}`}
              tabIndex={selected ? 0 : -1}
              className={
                tabClassName ||
                `px-3 py-2 text-sm rounded-t border transition-colors font-medium ` +
                  (selected
                    ? 'bg-white border-gray-300 border-b-transparent -mb-px'
                    : 'bg-gray-100 border-transparent hover:bg-gray-200')
              }
              onClick={() => activateTab(i)}
              type="button"
            >
              {tab.label}
            </button>
          );
        })}
      </div>
      {enabledTabs.map((tab, i) => {
        const selected = i === activeIndex;
        return (
          <div
            key={tab.id}
            role="tabpanel"
            id={`${baseId}-panel-${tab.id}`}
            aria-labelledby={`${baseId}-tab-${tab.id}`}
            hidden={!selected}
            className={panelClassName || 'border rounded p-4 bg-white'}
          >
            {selected && tab.content}
          </div>
        );
      })}
    </div>
  );
}
