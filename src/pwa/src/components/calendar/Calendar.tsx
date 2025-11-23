import { useMemo } from 'react';
import Button from '../Button';

export type CalendarEvent = {
  id: string | number;
  title: string;
  date: Date;
};

type CalendarProps = {
  month: Date; // any date within the month to render
  events?: CalendarEvent[];
  onPrevMonth?: () => void;
  onNextMonth?: () => void;
  onSelectDate?: (date: Date) => void;
};

function startOfMonth(d: Date) {
  return new Date(d.getFullYear(), d.getMonth(), 1);
}
function endOfMonth(d: Date) {
  return new Date(d.getFullYear(), d.getMonth() + 1, 0);
}

export default function Calendar({
  month,
  events = [],
  onPrevMonth,
  onNextMonth,
  onSelectDate,
}: CalendarProps) {
  const monthStart = startOfMonth(month);
  const monthEnd = endOfMonth(month);

  const days = useMemo(() => {
    // Build a 6x7 grid
    const startWeekday = monthStart.getDay(); // 0=Sun
    const gridStart = new Date(monthStart);
    gridStart.setDate(monthStart.getDate() - startWeekday);
    const out: Date[] = [];
    for (let i = 0; i < 42; i++) {
      const d = new Date(gridStart);
      d.setDate(gridStart.getDate() + i);
      out.push(d);
    }
    return out;
  }, [monthStart]);

  const eventsByDay = useMemo(() => {
    const map = new Map<string, CalendarEvent[]>();
    for (const e of events) {
      const key = `${e.date.getFullYear()}-${e.date.getMonth()}-${e.date.getDate()}`;
      const arr = map.get(key) || [];
      arr.push(e);
      map.set(key, arr);
    }
    return map;
  }, [events]);

  const monthLabel = month.toLocaleString(undefined, { month: 'long', year: 'numeric' });
  const weekdayLabels = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

  return (
    <div className="border rounded-lg overflow-hidden bg-white">
      <div className="flex items-center justify-between px-3 py-2 border-b bg-gray-50">
        <Button variant="ghost" size="sm" onClick={onPrevMonth} aria-label="Previous month">
          ‹
        </Button>
        <div className="text-sm font-medium">{monthLabel}</div>
        <Button variant="ghost" size="sm" onClick={onNextMonth} aria-label="Next month">
          ›
        </Button>
      </div>
      <div className="grid grid-cols-7 text-xs border-b">
        {weekdayLabels.map((w) => (
          <div key={w} className="px-2 py-1 text-center text-gray-500">
            {w}
          </div>
        ))}
      </div>
      <div className="grid grid-cols-7 gap-px bg-gray-200">
        {days.map((d, idx) => {
          const inMonth = d >= monthStart && d <= monthEnd;
          const key = `${d.getFullYear()}-${d.getMonth()}-${d.getDate()}`;
          const dayEvents = eventsByDay.get(key) || [];
          return (
            <button
              type="button"
              key={idx}
              onClick={() => onSelectDate?.(d)}
              className={`min-h-[84px] text-left bg-white px-2 py-1 focus:outline-none focus:ring-2 focus:ring-brand-accent ${
                inMonth ? '' : 'bg-gray-50 text-gray-400'
              }`}
            >
              <div className="text-[11px] mb-1">{d.getDate()}</div>
              <div className="space-y-1">
                {dayEvents.slice(0, 3).map((e) => (
                  <div
                    key={e.id}
                    className="truncate rounded bg-brand-secondary-100 text-[11px] px-1 py-0.5"
                  >
                    {e.title}
                  </div>
                ))}
                {dayEvents.length > 3 ? (
                  <div className="text-[10px] text-gray-500">+{dayEvents.length - 3} more</div>
                ) : null}
              </div>
            </button>
          );
        })}
      </div>
    </div>
  );
}
