/**
 * Activity type icon components
 * Icon mappings for each activity type using lucide-react
 */
import { Icon } from '../components/icons';

interface IconProps {
  className?: string;
}

/**
 * Feeding Icon
 */
export function FeedingIcon({ className = 'w-5 h-5' }: IconProps) {
  return <Icon name="feeding" className={className} size={20} />;
}

/**
 * Walk Icon
 */
export function WalkIcon({ className = 'w-5 h-5' }: IconProps) {
  return <Icon name="walk" className={className} size={20} />;
}

/**
 * Play Icon
 */
export function PlayIcon({ className = 'w-5 h-5' }: IconProps) {
  return <Icon name="play" className={className} size={20} />;
}

/**
 * Grooming Icon
 */
export function GroomingIcon({ className = 'w-5 h-5' }: IconProps) {
  return <Icon name="grooming" className={className} size={20} />;
}

/**
 * Vet Icon
 */
export function VetIcon({ className = 'w-5 h-5' }: IconProps) {
  return <Icon name="vet" className={className} size={20} />;
}

/**
 * Medication Icon
 */
export function MedicationIcon({ className = 'w-5 h-5' }: IconProps) {
  return <Icon name="medication" className={className} size={20} />;
}

/**
 * Training Icon - using heart as fallback for other
 */
export function TrainingIcon({ className = 'w-5 h-5' }: IconProps) {
  return <Icon name="other" className={className} size={20} />;
}

/**
 * Other Icon
 */
export function OtherIcon({ className = 'w-5 h-5' }: IconProps) {
  return <Icon name="other" className={className} size={20} />;
}

/**
 * Get icon component for activity type
 */
export function getActivityIcon(type: string): React.ComponentType<IconProps> {
  switch (type) {
    case 'feeding':
      return FeedingIcon;
    case 'walk':
      return WalkIcon;
    case 'play':
      return PlayIcon;
    case 'grooming':
      return GroomingIcon;
    case 'vet':
      return VetIcon;
    case 'medication':
      return MedicationIcon;
    case 'training':
      return TrainingIcon;
    default:
      return OtherIcon;
  }
}
