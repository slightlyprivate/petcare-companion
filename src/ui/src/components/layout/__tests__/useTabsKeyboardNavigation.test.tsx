import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { describe, it, expect } from 'vitest';
import Tabs, { TabDefinition } from '../Tabs';

function setup(tabs: TabDefinition[], orientation: 'horizontal' | 'vertical' = 'horizontal') {
  render(<Tabs tabs={tabs} orientation={orientation} />);
  const tabButtons = screen.getAllByRole('tab');
  return { tabButtons };
}

const sampleTabs: TabDefinition[] = [
  { id: 'one', label: 'One', content: <div>One Panel</div> },
  { id: 'two', label: 'Two', content: <div>Two Panel</div> },
  { id: 'three', label: 'Three', content: <div>Three Panel</div> },
];

describe('useTabsKeyboardNavigation - horizontal', () => {
  it('cycles right with ArrowRight and wraps to first', async () => {
    const { tabButtons } = setup(sampleTabs, 'horizontal');
    await userEvent.click(tabButtons[0]);
    expect(tabButtons[0]).toHaveAttribute('aria-selected', 'true');

    await userEvent.keyboard('{ArrowRight}');
    expect(tabButtons[1]).toHaveAttribute('aria-selected', 'true');

    await userEvent.keyboard('{ArrowRight}');
    expect(tabButtons[2]).toHaveAttribute('aria-selected', 'true');

    await userEvent.keyboard('{ArrowRight}');
    expect(tabButtons[0]).toHaveAttribute('aria-selected', 'true');
  });

  it('cycles left with ArrowLeft and wraps to last', async () => {
    const { tabButtons } = setup(sampleTabs, 'horizontal');
    await userEvent.click(tabButtons[0]);
    await userEvent.keyboard('{ArrowLeft}');
    expect(tabButtons[2]).toHaveAttribute('aria-selected', 'true');
  });

  it('Home moves to first and End moves to last', async () => {
    const { tabButtons } = setup(sampleTabs, 'horizontal');
    await userEvent.click(tabButtons[1]);
    await userEvent.keyboard('{Home}');
    expect(tabButtons[0]).toHaveAttribute('aria-selected', 'true');
    await userEvent.keyboard('{End}');
    expect(tabButtons[2]).toHaveAttribute('aria-selected', 'true');
  });
});

describe('useTabsKeyboardNavigation - vertical', () => {
  it('ArrowDown behaves like ArrowRight', async () => {
    const { tabButtons } = setup(sampleTabs, 'vertical');
    await userEvent.click(tabButtons[0]);
    await userEvent.keyboard('{ArrowDown}');
    expect(tabButtons[1]).toHaveAttribute('aria-selected', 'true');
  });

  it('ArrowUp behaves like ArrowLeft', async () => {
    const { tabButtons } = setup(sampleTabs, 'vertical');
    await userEvent.click(tabButtons[1]);
    await userEvent.keyboard('{ArrowUp}');
    expect(tabButtons[0]).toHaveAttribute('aria-selected', 'true');
  });
});
