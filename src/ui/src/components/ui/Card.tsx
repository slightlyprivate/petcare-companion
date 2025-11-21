import React from 'react';
import { cn } from '../../lib/cn';

/**
 * Card Component
 *
 * A reusable card component to display content in a bordered container with shadow.
 *
 * @param {string} [className] - Additional class names to apply to the card.
 * @param {React.ReactNode} children - The content to be displayed inside the card.
 * @returns {JSX.Element} The rendered card component.
 */
export function Card({
  className,
  children,
}: {
  className?: string;
  children: React.ReactNode;
}): JSX.Element {
  return <div className={cn('rounded-md border bg-white shadow-sm', className)}>{children}</div>;
}

/**
 * CardHeader Component
 *
 * A component to display the header section of a card.
 *
 * @param {string} [className] - Additional class names to apply to the header.
 * @param {React.ReactNode} children - The content to be displayed inside the header.
 * @returns {JSX.Element} The rendered card header component.
 */
export function CardHeader({
  className,
  children,
}: {
  className?: string;
  children: React.ReactNode;
}): JSX.Element {
  return <div className={cn('p-4 pb-2', className)}>{children}</div>;
}

/**
 * CardTitle Component
 *
 * A component to display the title of a card.
 *
 * @param {string} [className] - Additional class names to apply to the title.
 * @param {React.ReactNode} children - The content to be displayed as the title.
 * @returns {JSX.Element} The rendered card title component.
 */
export function CardTitle({
  className,
  children,
}: {
  className?: string;
  children: React.ReactNode;
}): JSX.Element {
  return <h2 className={cn('text-base font-semibold', className)}>{children}</h2>;
}

/**
 * CardDescription Component
 *
 * A component to display the description of a card.
 *
 * @param {string} [className] - Additional class names to apply to the description.
 * @param {React.ReactNode} children - The content to be displayed as the description.
 * @returns {JSX.Element} The rendered card description component.
 */
export function CardDescription({
  className,
  children,
}: {
  className?: string;
  children: React.ReactNode;
}): JSX.Element {
  return <p className={cn('text-sm text-gray-600', className)}>{children}</p>;
}

/**
 * CardContent Component
 *
 * A component to display the content section of a card.
 *
 * @param {string} [className] - Additional class names to apply to the content.
 * @param {React.ReactNode} children - The content to be displayed inside the content section.
 * @returns {JSX.Element} The rendered card content component.
 */
export function CardContent({
  className,
  children,
}: {
  className?: string;
  children: React.ReactNode;
}): JSX.Element {
  return <div className={cn('p-4 pt-0', className)}>{children}</div>;
}
