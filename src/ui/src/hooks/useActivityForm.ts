import { useState } from 'react';
import { useCreatePetActivity } from '../api/activities/hooks';

interface UseActivityFormOptions {
  petId: string | number;
  onSuccess?: () => void;
}

/**
 * Hook for managing activity creation form state and submission
 */
export function useActivityForm({ petId, onSuccess }: UseActivityFormOptions) {
  const [showAddForm, setShowAddForm] = useState(false);
  const [activityType, setActivityType] = useState('feeding');
  const [description, setDescription] = useState('');
  const [formError, setFormError] = useState('');

  const createActivity = useCreatePetActivity();

  const handleSubmit = async (mediaUrl?: string) => {
    setFormError('');

    if (!description.trim()) {
      setFormError('Description is required');
      return false;
    }

    try {
      await createActivity.mutateAsync({
        petId,
        type: activityType,
        description: description.trim(),
        media_url: mediaUrl?.trim() || null,
      });

      // Reset form
      setDescription('');
      setActivityType('feeding');
      setShowAddForm(false);
      onSuccess?.();

      return true;
    } catch (err) {
      setFormError((err as Error).message || 'Failed to create activity');
      return false;
    }
  };

  const toggleForm = () => {
    setShowAddForm(!showAddForm);
    if (showAddForm) {
      // Reset when closing
      setDescription('');
      setActivityType('feeding');
      setFormError('');
    }
  };

  const resetForm = () => {
    setDescription('');
    setActivityType('feeding');
    setFormError('');
    setShowAddForm(false);
  };

  return {
    showAddForm,
    activityType,
    description,
    formError,
    isSubmitting: createActivity.isPending,
    setActivityType,
    setDescription,
    handleSubmit,
    toggleForm,
    resetForm,
  };
}
