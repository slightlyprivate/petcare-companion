import { FormEvent, useState } from 'react';
import Button from '../Button';

export type AppointmentFormValues = {
  title: string;
  scheduled_at: string; // ISO string
  notes?: string;
};

type Props = {
  initial?: Partial<AppointmentFormValues>;
  onSubmit: (values: AppointmentFormValues) => void | Promise<void>;
  isSubmitting?: boolean;
};

/**
 * Form component for creating or editing an appointment.
 */
export default function AppointmentForm({ initial, onSubmit, isSubmitting }: Props) {
  const [title, setTitle] = useState(initial?.title || '');
  const [scheduledAt, setScheduledAt] = useState(initial?.scheduled_at || '');
  const [notes, setNotes] = useState(initial?.notes || '');

  function handleSubmit(e: FormEvent) {
    e.preventDefault();
    if (!title || !scheduledAt) return;
    void onSubmit({ title, scheduled_at: scheduledAt, notes: notes || undefined });
  }

  return (
    <form onSubmit={handleSubmit} className="space-y-3">
      <div className="space-y-1">
        <label className="text-sm">Title</label>
        <input
          className="w-full border rounded px-3 py-2"
          placeholder="Veterinary appointment"
          value={title}
          onChange={(e) => setTitle(e.target.value)}
          required
        />
      </div>
      <div className="space-y-1">
        <label className="text-sm">Scheduled At</label>
        <input
          type="datetime-local"
          className="w-full border rounded px-3 py-2"
          value={scheduledAt}
          onChange={(e) => setScheduledAt(e.target.value)}
          required
        />
      </div>
      <div className="space-y-1">
        <label className="text-sm">Notes (optional)</label>
        <textarea
          className="w-full border rounded px-3 py-2"
          placeholder="Any additional details"
          value={notes}
          onChange={(e) => setNotes(e.target.value)}
          rows={3}
        />
      </div>
      <div>
        <Button type="submit" isLoading={!!isSubmitting} disabled={!title || !scheduledAt}>
          Save Appointment
        </Button>
      </div>
    </form>
  );
}
