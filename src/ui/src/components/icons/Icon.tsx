import {
  Check,
  Pencil,
  Trash2,
  Plus,
  X,
  ChevronDown,
  ChevronUp,
  Calendar,
  Clock,
  User,
  Users,
  Loader2,
  AlertCircle,
  CheckCircle,
  XCircle,
  Info,
  Copy,
  UtensilsCrossed,
  Footprints,
  Gamepad2,
  Pill,
  Scissors,
  Stethoscope,
  Syringe,
  Heart,
  type LucideIcon,
} from 'lucide-react';
import { cn } from '../../lib/cn';

/**
 * Centralized icon registry using lucide-react
 * All icons are React components with consistent sizing and styling
 */
export const icons = {
  check: Check,
  pencil: Pencil,
  trash: Trash2,
  plus: Plus,
  close: X,
  copy: Copy,
  chevronDown: ChevronDown,
  chevronUp: ChevronUp,
  calendar: Calendar,
  clock: Clock,
  user: User,
  users: Users,
  spinner: Loader2,
  alertCircle: AlertCircle,
  checkCircle: CheckCircle,
  xCircle: XCircle,
  info: Info,
  feeding: UtensilsCrossed,
  walk: Footprints,
  play: Gamepad2,
  medication: Pill,
  grooming: Scissors,
  vet: Stethoscope,
  vaccination: Syringe,
  other: Heart,
} as const;

export type IconName = keyof typeof icons;

interface IconProps {
  name: IconName;
  className?: string;
  size?: number;
  strokeWidth?: number;
}

/**
 * Icon component wrapper for lucide-react icons
 * Provides consistent sizing and styling across the application
 */
export default function Icon({ name, className, size = 16, strokeWidth = 2 }: IconProps) {
  const LucideIcon = icons[name] as LucideIcon;

  return <LucideIcon className={cn(className)} size={size} strokeWidth={strokeWidth} />;
}
