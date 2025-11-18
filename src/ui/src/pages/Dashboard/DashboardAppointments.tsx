import { useMemo, useState } from 'react';
import Calendar, { type CalendarEvent } from '../../components/calendar/Calendar';
import QueryBoundary from '../../components/QueryBoundary';
import { useAppointmentsByPet, useCreateAppointment } from '../../api/appointments/hooks';
import { usePets } from '../../api/pets/hooks';
import type { Appointment, Pet } from '../../api/types';
import AppointmentForm from '../../components/forms/AppointmentForm';
import { useToast } from '../../lib/notifications';
import { ensureCsrf } from '../../lib/csrf';

/**
 * Dashboard appointments management page with an integrated calendar.
 */
export default function DashboardAppointments() {
  const toast = useToast();
  const pets = usePets();
  const petOptions = (pets.data?.data ?? []) as Pet[];
  const [petId, setPetId] = useState<string | ''>('');
  const [month, setMonth] = useState<Date>(new Date());
  const appts = useAppointmentsByPet(petId);
  const createAppt = useCreateAppointment();
  const [selectedDate, setSelectedDate] = useState<Date | null>(new Date());

  const events: CalendarEvent[] = useMemo(() => {
    const rows = (appts.data as { data?: Appointment[] } | undefined)?.data ?? [];
    return rows.map((r) => ({ id: r.id, title: r.title, date: new Date(r.scheduled_at) }));
  }, [appts.data]);

  async function onCreate(values: { title: string; scheduled_at: string; notes?: string }) {
    if (!petId) return;
    await ensureCsrf();
    createAppt.mutate(
      { petId, title: values.title, scheduled_at: values.scheduled_at, notes: values.notes },
      {
        onSuccess: () => toast.success('Appointment created'),
        onError: (e: unknown) =>
          toast.error((e as { message?: string } | undefined)?.message || 'Failed to create'),
      },
    );
  }

  return (
    <div className="space-y-4">
      <div className="flex items-center justify-between">
        <h1 className="text-xl font-semibold">Appointments</h1>
        <div className="flex items-center gap-2">
          <label className="text-sm">Pet</label>
          <select
            className="border rounded px-2 py-1 text-sm"
            value={petId}
            onChange={(e) => setPetId(e.target.value)}
          >
            <option value="">Select a pet</option>
            {petOptions.map((p) => (
              <option key={p.id} value={p.id}>
                {p.name}
              </option>
            ))}
          </select>
        </div>
      </div>

      <div className="grid gap-4 lg:grid-cols-3">
        <div className="lg:col-span-2">
          <QueryBoundary loading={appts.isLoading} error={appts.error}>
            <Calendar
              month={month}
              events={events}
              onPrevMonth={() => setMonth((m) => new Date(m.getFullYear(), m.getMonth() - 1, 1))}
              onNextMonth={() => setMonth((m) => new Date(m.getFullYear(), m.getMonth() + 1, 1))}
              onSelectDate={(d) => setSelectedDate(d)}
            />
          </QueryBoundary>
        </div>

        <div className="lg:col-span-1">
          <div className="border rounded-lg p-3 bg-white">
            <div className="text-sm font-medium mb-2">New Appointment</div>
            {!petId ? (
              <div className="text-xs text-gray-500">Select a pet to create an appointment.</div>
            ) : (
              <AppointmentForm
                isSubmitting={createAppt.isPending}
                initial={{
                  scheduled_at: selectedDate
                    ? `${selectedDate.getFullYear()}-${String(selectedDate.getMonth() + 1).padStart(2, '0')}-${String(selectedDate.getDate()).padStart(2, '0')}T00:00`
                    : '',
                }}
                onSubmit={onCreate}
              />
            )}
          </div>
        </div>
      </div>
    </div>
  );
}
