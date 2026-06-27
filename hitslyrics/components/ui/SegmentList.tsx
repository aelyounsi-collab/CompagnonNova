'use client';

import { AudioSegment } from '@/types';

const INTENSITY_COLORS: Record<string, string> = {
  soft: 'text-blue-400 bg-blue-400/10 border-blue-400/30',
  medium: 'text-amber-400 bg-amber-400/10 border-amber-400/30',
  powerful: 'text-red-400 bg-red-400/10 border-red-400/30',
};

const INTENSITY_ICONS: Record<string, string> = {
  soft: '🌊',
  medium: '🔥',
  powerful: '⚡',
};

interface SegmentListProps {
  segments: AudioSegment[];
}

function formatTime(seconds: number): string {
  const m = Math.floor(seconds / 60);
  const s = Math.floor(seconds % 60);
  return `${m}:${s.toString().padStart(2, '0')}`;
}

export default function SegmentList({ segments }: SegmentListProps) {
  return (
    <div className="space-y-2">
      <h3 className="text-sm font-semibold text-zinc-300 mb-3">
        Audio Segments ({segments.length})
      </h3>
      <div className="space-y-2 max-h-64 overflow-y-auto pr-1">
        {segments.map((seg) => (
          <div
            key={seg.index}
            className={`flex items-center gap-3 p-3 rounded-lg border ${INTENSITY_COLORS[seg.intensity]}`}
          >
            <span className="text-base flex-shrink-0">{INTENSITY_ICONS[seg.intensity]}</span>
            <div className="flex-1 min-w-0">
              <div className="flex items-center justify-between gap-2">
                <span className="text-xs font-mono text-zinc-300">
                  {formatTime(seg.startTime)} → {formatTime(seg.endTime)}
                </span>
                <span className="text-xs font-semibold capitalize flex-shrink-0">
                  {seg.intensity}
                </span>
              </div>
              {/* RMS intensity bar */}
              <div className="mt-1.5 h-1.5 bg-zinc-800 rounded-full overflow-hidden">
                <div
                  className={`h-full rounded-full transition-all ${
                    seg.intensity === 'powerful' ? 'bg-red-500' :
                    seg.intensity === 'medium' ? 'bg-amber-500' : 'bg-blue-500'
                  }`}
                  style={{ width: `${Math.round(seg.rms * 100)}%` }}
                />
              </div>
            </div>
            <span className="text-xs text-zinc-600 flex-shrink-0">
              #{seg.index + 1}
            </span>
          </div>
        ))}
      </div>
    </div>
  );
}
