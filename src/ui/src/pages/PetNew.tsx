/**
 * Pet creation page.
 * @returns Pet creation component
 */
export default function PetNew() {
  return (
    <div>
      <h1 className="text-xl font-semibold mb-2">Add a New Pet</h1>
      <div className="text-sm text-brand-fg mb-4">Reusable form component placeholder.</div>
      <form className="space-y-3">
        <div className="space-y-1">
          <label className="text-sm">Name</label>
          <input className="w-full border rounded px-3 py-2" placeholder="Pet name" />
        </div>
        <div className="space-y-1">
          <label className="text-sm">Species</label>
          <input className="w-full border rounded px-3 py-2" placeholder="Dog, Cat, ..." />
        </div>
        <button className="px-4 py-2 rounded bg-brand-accent text-white" type="button">
          Save
        </button>
      </form>
    </div>
  );
}
