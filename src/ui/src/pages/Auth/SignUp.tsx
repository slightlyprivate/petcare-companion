/**
 * Sign up page for the PetCare Companion application.
 * @returns Sign up page component
 */
import TextInput from '../../components/TextInput';
import Button from '../../components/Button';

export default function SignUp() {
  return (
    <div>
      <h1 className="text-xl font-semibold mb-2">Create your account</h1>
      <p className="text-sm text-brand-fg mb-4">Email registration flow placeholder.</p>
      <form className="space-y-3">
        <TextInput label="Email" type="email" placeholder="you@example.com" />
        <Button type="button" className="w-full">
          Continue
        </Button>
      </form>
    </div>
  );
}
