/**
 * Sign up page for the PetCare Companion application.
 * @returns Sign up page component
 */
export default function SignUp() {
  return (
    <div>
      <h1 className="text-xl font-semibold mb-2">Create your account</h1>
      <p className="text-sm text-brand-fg mb-4">Email registration flow placeholder.</p>
      <form className="space-y-3">
        <div className="space-y-1">
          <label className="text-sm">Email</label>
          <input
            className="w-full border rounded px-3 py-2"
            type="email"
            placeholder="you@example.com"
          />
        </div>
        <button type="button" className="w-full px-4 py-2 rounded bg-brand-accent text-white">
          Continue
        </button>
      </form>
    </div>
  );
}
