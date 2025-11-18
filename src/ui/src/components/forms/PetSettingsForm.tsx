import { FormEvent, useEffect, useState } from 'react';
import Button from '../Button';

export type PetSettingsValues = {
  is_public: boolean;
  share_slug?: string;
  allow_comments?: boolean;
};

type PetSettingsFormProps = {
  initial?: Partial<PetSettingsValues>;
  onSubmit: (values: PetSettingsValues) => void | Promise<void>;
  isSubmitting?: boolean;
};

export default function PetSettingsForm({ initial, onSubmit, isSubmitting }: PetSettingsFormProps) {
  const [values, setValues] = useState<PetSettingsValues>({
    is_public: false,
    share_slug: '',
    allow_comments: false,
    ...initial,
  });

  useEffect(() => {
    setValues((v) => ({ ...v, ...initial }));
  }, [initial?.is_public, initial?.share_slug, initial?.allow_comments]);

  function handleSubmit(e: FormEvent) {
    e.preventDefault();
    void onSubmit({ ...values, share_slug: values.share_slug || undefined });
  }

  return (
    <form onSubmit={handleSubmit} className="space-y-4">
      <div className="flex items-center gap-3">
        <input
          id="is_public"
          type="checkbox"
          className="h-4 w-4"
          checked={values.is_public}
          onChange={(e) => setValues((v) => ({ ...v, is_public: e.target.checked }))}
        />
        <label htmlFor="is_public" className="text-sm">
          Make pet profile public
        </label>
      </div>

      <div className="flex items-center gap-3">
        <input
          id="allow_comments"
          type="checkbox"
          className="h-4 w-4"
          checked={values.allow_comments || false}
          onChange={(e) => setValues((v) => ({ ...v, allow_comments: e.target.checked }))}
        />
        <label htmlFor="allow_comments" className="text-sm">
          Allow comments on public profile
        </label>
      </div>

      <div className="space-y-1">
        <label className="text-sm" htmlFor="share_slug">
          Public URL slug (optional)
        </label>
        <input
          id="share_slug"
          className="w-full border rounded px-3 py-2"
          placeholder="e.g. bella-the-cat"
          value={values.share_slug || ''}
          onChange={(e) => setValues((v) => ({ ...v, share_slug: e.target.value }))}
        />
        <p className="text-xs text-gray-500">Used for the SEO-friendly public pet page.</p>
      </div>

      <div>
        <Button type="submit" isLoading={!!isSubmitting}>
          Save Settings
        </Button>
      </div>
    </form>
  );
}
