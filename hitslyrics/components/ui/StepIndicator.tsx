'use client';

type Step = {
  label: string;
  key: string;
};

const STEPS: Step[] = [
  { label: 'Upload', key: 'upload' },
  { label: 'Analyze', key: 'analyze' },
  { label: 'Prepare', key: 'prepare' },
  { label: 'Generate', key: 'generate' },
  { label: 'Export', key: 'export' },
];

interface StepIndicatorProps {
  currentStep: string;
}

export default function StepIndicator({ currentStep }: StepIndicatorProps) {
  const currentIdx = STEPS.findIndex((s) => s.key === currentStep);

  return (
    <div className="flex items-center justify-center gap-0 w-full max-w-2xl mx-auto">
      {STEPS.map((step, idx) => {
        const isDone = idx < currentIdx;
        const isActive = idx === currentIdx;

        return (
          <div key={step.key} className="flex items-center">
            <div className="flex flex-col items-center">
              <div
                className={`w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold border-2 transition-all ${
                  isDone
                    ? 'bg-amber-500 border-amber-500 text-black'
                    : isActive
                    ? 'bg-transparent border-amber-400 text-amber-400'
                    : 'bg-transparent border-zinc-600 text-zinc-600'
                }`}
              >
                {isDone ? '✓' : idx + 1}
              </div>
              <span
                className={`text-xs mt-1 font-medium ${
                  isDone ? 'text-amber-500' : isActive ? 'text-amber-400' : 'text-zinc-600'
                }`}
              >
                {step.label}
              </span>
            </div>
            {idx < STEPS.length - 1 && (
              <div
                className={`h-px w-12 mx-1 mb-4 transition-all ${
                  idx < currentIdx ? 'bg-amber-500' : 'bg-zinc-700'
                }`}
              />
            )}
          </div>
        );
      })}
    </div>
  );
}
