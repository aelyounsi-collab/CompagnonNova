import StudioWorkflow from '@/components/StudioWorkflow';

export default function Home() {
  return (
    <div className="min-h-screen bg-zinc-950">
      {/* Header */}
      <header className="border-b border-zinc-800 bg-zinc-950/80 backdrop-blur-sm sticky top-0 z-10">
        <div className="max-w-4xl mx-auto px-4 py-4 flex items-center justify-between">
          <div className="flex items-center gap-3">
            <span className="text-2xl">🎙️</span>
            <div>
              <h1 className="text-lg font-bold text-zinc-100 leading-none">HitsLyrics</h1>
              <p className="text-xs text-zinc-500 mt-0.5">AI-powered lip sync studio · V1</p>
            </div>
          </div>
          <span className="text-xs font-mono bg-amber-500/10 text-amber-400 border border-amber-500/20 px-2 py-1 rounded-full">
            beta
          </span>
        </div>
      </header>

      {/* Hero */}
      <div className="bg-gradient-to-b from-zinc-900 to-zinc-950 border-b border-zinc-800">
        <div className="max-w-4xl mx-auto px-4 py-10 text-center space-y-3">
          <h2 className="text-3xl md:text-4xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-amber-400 to-orange-500">
            Bring Your Music to Life
          </h2>
          <p className="text-zinc-400 max-w-xl mx-auto text-base">
            Upload a portrait and your song. We analyze the vocal intensity, segment the audio, and prepare a professional lip-sync job — gospel, R&B, pop, and more.
          </p>
          <div className="flex items-center justify-center gap-6 pt-2 text-sm text-zinc-600">
            <span>🖼️ Portrait upload</span>
            <span>🎵 Audio analysis</span>
            <span>⚡ Intensity detection</span>
            <span>🎬 9:16 export ready</span>
          </div>
        </div>
      </div>

      {/* Main workflow */}
      <main className="max-w-4xl mx-auto px-4 py-10">
        <StudioWorkflow />
      </main>

      {/* Footer */}
      <footer className="border-t border-zinc-800 mt-16">
        <div className="max-w-4xl mx-auto px-4 py-6 text-center text-xs text-zinc-600">
          HitsLyrics · V1 · Built with Next.js + TypeScript ·{' '}
          <span className="text-zinc-700">Phase 2: MuseTalk · Phase 3: LivePortrait · Phase 4: Sync.so</span>
        </div>
      </footer>
    </div>
  );
}
