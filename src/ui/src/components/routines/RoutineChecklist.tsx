import { useTodayTasks, useCompleteRoutineOccurrence } from '../../api/routines/hooks';
import { useRoutineModal } from '../../hooks/useRoutineModal';
import ErrorMessage from '../ErrorMessage';
import Spinner from '../Spinner';
import Button from '../Button';
import Modal from '../modals/Modal';
import ConfirmDialog from '../modals/ConfirmDialog';
import RoutineForm from './RoutineForm';
import RoutineEmptyState from './RoutineEmptyState';
import RoutineProgress from './RoutineProgress';
import RoutineTaskCard from './RoutineTaskCard';

interface RoutineChecklistProps {
  petId: string | number;
  canComplete?: boolean;
  canManage?: boolean; // owner can create/edit/delete
}

/**
 * Daily routine checklist component showing today's tasks with completion tracking.
 */
export default function RoutineChecklist({
  petId,
  canComplete = false,
  canManage = false,
}: RoutineChecklistProps) {
  const { data: tasksData, isLoading, error } = useTodayTasks(petId);
  const completeTask = useCompleteRoutineOccurrence();
  const modal = useRoutineModal(petId);

  const handleComplete = async (occurrenceId: string | number) => {
    try {
      await completeTask.mutateAsync(occurrenceId);
    } catch (err) {
      console.error('Failed to complete task:', err);
    }
  };

  if (isLoading) {
    return (
      <div className="flex items-center justify-center py-8">
        <Spinner />
      </div>
    );
  }

  if (error) {
    return <ErrorMessage message="Failed to load today's tasks" />;
  }

  const tasks = tasksData?.data || [];
  const completedCount = tasks.filter((t) => t.completed_at).length;
  const totalCount = tasks.length;

  return (
    <div className="space-y-4">
      <div className="flex items-center justify-between">
        <div>
          <h2 className="text-lg font-semibold text-brand-fg">Today's Routines</h2>
          {totalCount > 0 && (
            <p className="text-sm text-brand-fg/60">
              {completedCount} of {totalCount} completed
            </p>
          )}
        </div>
        <div className="flex items-center gap-3">
          <RoutineProgress completed={completedCount} total={totalCount} />
          {canManage && (
            <Button variant="secondary" size="sm" onClick={modal.openCreate}>
              Create Routine
            </Button>
          )}
        </div>
      </div>

      <div className="space-y-2">
        {tasks.length === 0 ? (
          canManage ? (
            <RoutineEmptyState canCreate={canManage} onCreateClick={modal.openCreate} />
          ) : (
            <div className="rounded-lg border border-brand-muted bg-brand-secondary/20 p-6 text-center">
              <p className="text-sm text-brand-fg/60">No routines scheduled for today</p>
            </div>
          )
        ) : (
          tasks.map((task) => (
            <RoutineTaskCard
              key={task.id}
              task={task}
              canComplete={canComplete}
              canManage={canManage}
              isCompleting={completeTask.isPending}
              onComplete={handleComplete}
              onEdit={modal.openEdit}
              onDelete={modal.deleteState.confirmDelete}
            />
          ))
        )}
      </div>

      {completedCount === totalCount && totalCount > 0 && (
        <div className="rounded-lg border border-green-200 bg-green-50 p-4 text-center">
          <p className="text-sm font-medium text-green-800">🎉 All tasks completed for today!</p>
        </div>
      )}

      {/* Create/Edit Modal */}
      <Modal
        isOpen={modal.isOpen}
        onClose={modal.close}
        title={modal.isEditMode ? 'Edit Routine' : 'Create Routine'}
      >
        <RoutineForm
          name={modal.formState.name}
          description={modal.formState.description}
          timeOfDay={modal.formState.timeOfDay}
          daysOfWeek={modal.formState.daysOfWeek}
          formError={modal.formState.error}
          isSubmitting={modal.isSubmitting}
          onNameChange={modal.setName}
          onDescriptionChange={modal.setDescription}
          onTimeOfDayChange={modal.setTimeOfDay}
          onDaysOfWeekChange={modal.setDaysOfWeek}
          onToggleDayOfWeek={modal.toggleDayOfWeek}
          onSubmit={modal.handleSubmit}
          onCancel={modal.close}
          submitLabel={modal.isEditMode ? 'Update Routine' : 'Create Routine'}
        />
      </Modal>

      {/* Delete Confirmation */}
      <ConfirmDialog
        isOpen={modal.deleteState.isConfirmOpen}
        title="Delete Routine"
        message="Are you sure you want to delete this routine? This will delete all scheduled tasks for this routine."
        confirmText="Delete"
        onConfirm={modal.deleteState.executeDelete}
        onClose={modal.deleteState.cancelDelete}
        variant="danger"
        isLoading={modal.deleteState.isDeleting}
      />
    </div>
  );
}
