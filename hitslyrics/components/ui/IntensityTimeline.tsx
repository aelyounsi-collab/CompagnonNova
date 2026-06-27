'use client';

import { AudioSegment } from '@/types';

interface IntensityTimelineProps {
  segments: AudioSegment[];
  totalDuration: number;
}

const INTENSITY_BAR_COLORS: Record<string, string> = {
  soft: 'bg-blue-500',
  medium: 'bg-amber-500',
  powerful: 'bg-red-500',
};

export default function IntensityTimeline({ segments, totalDuration }: IntensityTimelineProps) {
  if (!segments.length || !totalDuration) return null;

  return (
    <div>
      <h3 className="text-sm font-semibold text-zinc-300 mb-2">Volume / Intensity Timeline</h3>
      <div className="relative h-16 bg-zinc-900 rounded-lg border border-zinc-800 overflow-hidden">
        {segments.map((seg) => {
          const leftPct = (seg.startTime / totalDuration) * 100;
          const widthPct = (seg.duration / totalDuration) * 100;
          const heightPct = Math.max(15, seg.rms * 100);

          return (
            <div
              key={seg.index}
              title={`Segment ${seg.index + 1}: ${seg.intensity} (${Math.round(seg.rms * 100)}%)`}
              className={`absolute bottom-0 ${INTENSITY_BAR_COLORS[seg.intensity]} opacity-80 hover:opacity-100 transition-opacity`}
              style={{
                left: `${leftPct}%`,
                width: `calc(${widthPct}% - 2px)`,
                height: `${heightPct}%`,
              }}
            />
          );
        })}
        {/* Axis labels */}
        <div className="absolute bottom-0 left-0 right-0 flex justify-between px-2 pb-0.5 pointer-events-none">
          <span className="text-[10px] text-zinc-600">0:00</span>
          <span className="text-[10px] text-zinc-600">
            {Math.floor(totalDuration / 60)}:{String(Math.floor(totalDuration % 60)).padStart(2, '0')}
          </span>
        </div>
      </div>
      {/* Legend */}
      <div className="flex items-center gap-4 mt-2">
        {(['soft', 'medium', 'powerful'] as const).map((level) => (
          <div key={level} className="flex items-center gap-1.5">
            <div className={`w-3 h-3 rounded-sm ${INTENSITY_BAR_COLORS[level]}`} />
            <span className="text-xs text-zinc-500 capitalize">{level}</span>
          </div>
        ))}
      </div>
    </div>
  );
}
