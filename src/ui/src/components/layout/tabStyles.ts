import { cva } from 'class-variance-authority';

export const tabButtonStyles = cva(
  'px-3 py-2 text-sm font-medium transition-colors border rounded-t focus:outline-none focus:ring-2 focus:ring-primary/40',
  {
    variants: {
      selected: {
        true: 'bg-white border-gray-300 border-b-transparent -mb-px',
        false: 'bg-gray-100 border-transparent hover:bg-gray-200',
      },
      orientation: {
        horizontal: '',
        vertical: 'rounded-none rounded-l',
      },
    },
    compoundVariants: [
      {
        selected: true,
        orientation: 'vertical',
        class: 'border-r-transparent -mr-px',
      },
    ],
  },
);
