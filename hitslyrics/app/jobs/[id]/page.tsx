import { getJobById } from '@/lib/jobs/store';
import { notFound } from 'next/navigation';
import Link from 'next/link';
import SegmentList from '@/components/ui/SegmentList';
import IntensityTimeline from '@/components/ui/IntensityTimeline';
import StepIndicator from '@/components/ui/StepIndicator';

const STATUS_COLORS: Record<string, string> = {
  pending: 'bg-amber-400/10 text-amber-400 border-amber-400/30',
  analyzing: 'bg-blue-400/10 text-blue-400 border-blue-400/30',
  ready: 'bg-green-400/10 text-green-400 border-green-400/30',
  processing: 'bg-purple-400/10 text-purple-400 border-purple-400/30',
  completed: 'bg-emerald-400/10 text-emerald-400 border-emerald-400/30',
  failed: 'bg-red-400/10 text-red-400 border-red-400/30',
};

const ENGINE_INFO = {
  none: { label: 'No engine selected', icon: '⚙️' },
  musetalk: { label: 'MuseTalk (Open Source)', icon: '🤖' },
  liveportrait: { label: 'LivePortrait', icon: '🎭' },
  syncso: { label: 'Sync.so API', icon: '☁️' },
};

export default async function JobPage({ params }: { params: Promise<{ id: string }> }) {
  const { id } = await params;
  const job = getJobById(id);

  if (!job) notFound();

  const analysis = job.analysis;
  const powerfulSegments = analysis?.segments.filter((s) => s.intensity === 'powerful') ?? [];

  return (
    <div className="min-h-screen bg-zinc-950 text-zinc-100">
      <div className="max-w-4xl mx-auto px-4 py-8 space-y-8">
        {/* Header */}
        <div className="flex items-center justify-between">
          <Link href="/" className="text-sm text-zinc-500 hover:text-amber-400 transition-colors">
            ← Back to Studio
          </Link>
          <h1 className="text-lg font-bold text-amber-400">HitsLyrics</h1>
        </div>

        <StepIndicator currentStep="generate" />

        {/* Job header */}
        <div className="bg-zinc-900 rounded-2xl border border-zinc-800 p-6 space-y-4">
          <div className="flex items-start justify-between gap-4 flex-wrap">
            <div>
              <h2 className="text-xl font-bold text-zinc-100">Lip Sync Job</h2>
              <p className="text-xs text-zinc-600 font-mono mt-1">{job.id}</p>
            </div>
            <span className={`px-3 py-1 rounded-full border text-sm font-semibold capitalize ${STATUS_COLORS[job.status] ?? ''}`}>
              {job.status}
            </span>
          </div>

          <div className="grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
            {[
              { label: 'Avatar', value: job.avatarOriginalName, icon: '🖼️' },
              { label: 'Audio', value: job.audioOriginalName, icon: '🎵' },
              { label: 'Duration', value: analysis ? `${Math.floor(analysis.duration / 60)}:${String(Math.floor(analysis.duration % 60)).padStart(2, '0')}` : '—', icon: '⏱️' },
              { label: 'Segments', value: analysis?.segments.length ?? '—', icon: '📋' },
            ].map((item) => (
              <div key={item.label} className="bg-zinc-800 rounded-xl p-3">
                <p className="text-xs text-zinc-500">{item.icon} {item.label}</p>
                <p className="text-zinc-200 font-medium mt-1 truncate text-sm">{item.value}</p>
              </div>
            ))}
          </div>

          {/* Progress bar */}
          <div>
            <div className="flex justify-between text-xs text-zinc-500 mb-1">
              <span>Progress</span>
              <span>{job.progress}%</span>
            </div>
            <div className="h-2 bg-zinc-800 rounded-full overflow-hidden">
              <div
                className="h-full bg-amber-500 rounded-full transition-all"
                style={{ width: `${job.progress}%` }}
              />
            </div>
          </div>
        </div>

        {/* Engine placeholder section */}
        <div className="bg-zinc-900 rounded-2xl border border-zinc-800 p-6 space-y-4">
          <h3 className="text-lg font-semibold text-zinc-200 flex items-center gap-2">
            🤖 Lip Sync Engine
          </h3>

          {/* TODO: Phase 2 – Connect MuseTalk engine */}
          {/* TODO: Phase 3 – Add LivePortrait for expression control */}
          {/* TODO: Phase 4 – Add Sync.so API integration */}
          <div className="bg-zinc-800/50 border border-dashed border-zinc-700 rounded-xl p-6 text-center space-y-3">
            <p className="text-2xl">🔌</p>
            <p className="font-semibold text-zinc-300">Lip sync engine will be connected here</p>
            <p className="text-sm text-zinc-500">Select an engine below, then click Generate</p>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-3 gap-3">
            {(Object.entries(ENGINE_INFO) as [string, { label: string; icon: string }][])
              .filter(([key]) => key !== 'none')
              .map(([key, info]) => (
                <div
                  key={key}
                  className="bg-zinc-800 rounded-xl p-4 border border-zinc-700 hover:border-amber-500/50 transition-colors cursor-not-allowed opacity-60"
                  title="Coming in Phase 2-4"
                >
                  <div className="flex items-center gap-2 mb-2">
                    <span className="text-xl">{info.icon}</span>
                    <span className="text-sm font-semibold text-zinc-300">{info.label}</span>
                  </div>
                  <span className="text-xs text-zinc-600 bg-zinc-900 rounded px-2 py-0.5">
                    Coming soon
                  </span>
                </div>
              ))}
          </div>

          <button
            disabled
            className="w-full py-4 rounded-xl bg-zinc-800 text-zinc-600 font-bold text-base cursor-not-allowed"
          >
            🎬 Generate Lip Sync Video (engine not connected)
          </button>
        </div>

        {/* Analysis results */}
        {analysis && (
          <div className="bg-zinc-900 rounded-2xl border border-zinc-800 p-6 space-y-6">
            <h3 className="text-lg font-semibold text-zinc-200">Analysis Results</h3>

            {powerfulSegments.length > 0 && (
              <div className="bg-red-500/5 border border-red-500/20 rounded-xl p-4">
                <h4 className="text-sm font-semibold text-red-400 mb-2">
                  ⚡ {powerfulSegments.length} Powerful Vocal Moment{powerfulSegments.length > 1 ? 's' : ''} Detected
                </h4>
                <p className="text-xs text-zinc-500">
                  These sections will trigger wider mouth opening, stronger jaw movement, and higher emotional expression intensity in the lip-sync engine.
                </p>
              </div>
            )}

            <IntensityTimeline segments={analysis.segments} totalDuration={analysis.duration} />
            <SegmentList segments={analysis.segments} />
          </div>
        )}

        {/* Job JSON export */}
        <details className="bg-zinc-900 rounded-2xl border border-zinc-800">
          <summary className="px-6 py-4 cursor-pointer text-sm text-zinc-500 hover:text-zinc-300 transition-colors">
            View raw job JSON
          </summary>
          <pre className="px-6 pb-6 text-xs text-zinc-400 overflow-auto max-h-64 font-mono">
            {JSON.stringify(job, null, 2)}
          </pre>
        </details>
      </div>
    </div>
  );
}
