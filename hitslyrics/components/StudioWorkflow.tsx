'use client';

import { useState, useCallback } from 'react';
import { useRouter } from 'next/navigation';
import UploadCard from './ui/UploadCard';
import AvatarPreview from './ui/AvatarPreview';
import AudioPlayer from './ui/AudioPlayer';
import SegmentList from './ui/SegmentList';
import IntensityTimeline from './ui/IntensityTimeline';
import StepIndicator from './ui/StepIndicator';
import { AudioAnalysis, LipSyncJob } from '@/types';

type WorkflowStep = 'upload' | 'analyze' | 'prepare' | 'generate' | 'export';

interface UploadedFiles {
  avatarUrl: string;
  avatarPath: string;
  avatarName: string;
  audioUrl: string;
  audioPath: string;
  audioName: string;
}

export default function StudioWorkflow() {
  const router = useRouter();
  const [step, setStep] = useState<WorkflowStep>('upload');
  const [uploading, setUploading] = useState(false);
  const [analyzing, setAnalyzing] = useState(false);
  const [preparing, setPreparing] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const [files, setFiles] = useState<Partial<UploadedFiles>>({});
  const [analysis, setAnalysis] = useState<AudioAnalysis | null>(null);
  const [job, setJob] = useState<LipSyncJob | null>(null);

  const [pendingAvatar, setPendingAvatar] = useState<File | null>(null);
  const [pendingAudio, setPendingAudio] = useState<File | null>(null);
  const [avatarPreviewUrl, setAvatarPreviewUrl] = useState<string | null>(null);
  const [audioPreviewUrl, setAudioPreviewUrl] = useState<string | null>(null);

  const handleAvatarFile = useCallback((file: File) => {
    setError(null);
    setPendingAvatar(file);
    const url = URL.createObjectURL(file);
    setAvatarPreviewUrl(url);
  }, []);

  const handleAudioFile = useCallback((file: File) => {
    setError(null);
    setPendingAudio(file);
    const url = URL.createObjectURL(file);
    setAudioPreviewUrl(url);
  }, []);

  async function handleUpload() {
    if (!pendingAvatar || !pendingAudio) {
      setError('Please select both an avatar image and an audio file.');
      return;
    }

    setUploading(true);
    setError(null);

    try {
      const formData = new FormData();
      formData.append('avatar', pendingAvatar);
      formData.append('audio', pendingAudio);

      const res = await fetch('/api/upload', { method: 'POST', body: formData });
      const data = await res.json();

      if (!data.success) throw new Error(data.error ?? 'Upload failed');

      setFiles({
        avatarUrl: avatarPreviewUrl!,
        avatarPath: data.avatarPath,
        avatarName: pendingAvatar.name,
        audioUrl: audioPreviewUrl!,
        audioPath: data.audioPath,
        audioName: pendingAudio.name,
      });
      setStep('analyze');
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Upload failed');
    } finally {
      setUploading(false);
    }
  }

  async function handleAnalyze() {
    if (!files.audioPath) return;

    setAnalyzing(true);
    setError(null);

    try {
      const res = await fetch('/api/analyze', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ audioPath: files.audioPath }),
      });
      const data = await res.json();
      if (!data.success) throw new Error(data.error ?? 'Analysis failed');
      setAnalysis(data.analysis);
      setStep('prepare');
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Analysis failed');
    } finally {
      setAnalyzing(false);
    }
  }

  async function handlePrepare() {
    if (!files.avatarPath || !files.audioPath || !analysis) return;

    setPreparing(true);
    setError(null);

    try {
      const res = await fetch('/api/jobs', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          avatarPath: files.avatarPath,
          audioPath: files.audioPath,
          avatarOriginalName: files.avatarName,
          audioOriginalName: files.audioName,
          analysis,
        }),
      });
      const data = await res.json();
      if (!data.success) throw new Error(data.error ?? 'Job creation failed');
      setJob(data.job);
      router.push(`/jobs/${data.job.id}`);
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Failed to prepare job');
    } finally {
      setPreparing(false);
    }
  }

  const powerfulCount = analysis?.segments.filter((s) => s.intensity === 'powerful').length ?? 0;
  const canUpload = pendingAvatar && pendingAudio && !uploading;

  return (
    <div className="space-y-8">
      <StepIndicator currentStep={step} />

      {error && (
        <div className="bg-red-900/30 border border-red-700 text-red-300 rounded-xl px-4 py-3 text-sm">
          {error}
        </div>
      )}

      {/* STEP: Upload */}
      <section className="space-y-6">
        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
          {/* Avatar upload */}
          <div className="space-y-4">
            <h2 className="text-sm font-semibold text-zinc-400 uppercase tracking-wider">
              1. Avatar Portrait
            </h2>
            {avatarPreviewUrl ? (
              <div className="flex items-center gap-4 p-4 bg-zinc-900 rounded-xl border border-zinc-700">
                <AvatarPreview src={avatarPreviewUrl} filename={pendingAvatar?.name ?? ''} />
                <div className="flex-1 space-y-2">
                  <p className="text-sm font-medium text-zinc-200">{pendingAvatar?.name}</p>
                  <p className="text-xs text-green-400">✓ Ready</p>
                  <button
                    onClick={() => { setPendingAvatar(null); setAvatarPreviewUrl(null); }}
                    disabled={step !== 'upload'}
                    className="text-xs text-zinc-500 hover:text-red-400 transition-colors disabled:pointer-events-none"
                  >
                    Remove
                  </button>
                </div>
              </div>
            ) : (
              <UploadCard
                label="Upload Avatar Image"
                accept="image/jpeg,image/png,image/webp"
                icon="🖼️"
                hint="Portrait photo · JPEG, PNG, WebP · max 10MB"
                onFile={handleAvatarFile}
                disabled={step !== 'upload'}
              />
            )}
          </div>

          {/* Audio upload */}
          <div className="space-y-4">
            <h2 className="text-sm font-semibold text-zinc-400 uppercase tracking-wider">
              2. Gospel Song / Audio
            </h2>
            {audioPreviewUrl ? (
              <div className="space-y-3 p-4 bg-zinc-900 rounded-xl border border-zinc-700">
                <AudioPlayer src={audioPreviewUrl} filename={pendingAudio?.name ?? ''} />
                <div className="flex items-center justify-between">
                  <p className="text-xs text-green-400">✓ Ready</p>
                  <button
                    onClick={() => { setPendingAudio(null); setAudioPreviewUrl(null); }}
                    disabled={step !== 'upload'}
                    className="text-xs text-zinc-500 hover:text-red-400 transition-colors disabled:pointer-events-none"
                  >
                    Remove
                  </button>
                </div>
              </div>
            ) : (
              <UploadCard
                label="Upload Audio / Song"
                accept="audio/*"
                icon="🎵"
                hint="Gospel song · MP3, WAV, AAC, OGG · max 100MB"
                onFile={handleAudioFile}
                disabled={step !== 'upload'}
              />
            )}
          </div>
        </div>

        {step === 'upload' && (
          <button
            onClick={handleUpload}
            disabled={!canUpload}
            className="w-full py-4 rounded-xl bg-amber-500 hover:bg-amber-400 disabled:bg-zinc-800 disabled:text-zinc-600 text-black font-bold text-base transition-colors"
          >
            {uploading ? 'Uploading…' : 'Upload Files →'}
          </button>
        )}
      </section>

      {/* STEP: Analyze */}
      {(step === 'analyze' || step === 'prepare' || step === 'generate') && (
        <section className="space-y-4">
          <div className="h-px bg-zinc-800" />
          <h2 className="text-sm font-semibold text-zinc-400 uppercase tracking-wider">
            3. Audio Analysis
          </h2>

          {!analysis ? (
            <div className="bg-zinc-900 rounded-xl p-6 border border-zinc-800 text-center space-y-4">
              <p className="text-zinc-400 text-sm">
                Analyze audio duration, volume curve, and segment the track into optimal 10–15 second clips.
              </p>
              <button
                onClick={handleAnalyze}
                disabled={analyzing}
                className="px-8 py-3 rounded-xl bg-amber-500 hover:bg-amber-400 disabled:bg-zinc-800 disabled:text-zinc-600 text-black font-bold transition-colors"
              >
                {analyzing ? 'Analyzing…' : '🎙 Analyze Audio'}
              </button>
            </div>
          ) : (
            <div className="bg-zinc-900 rounded-xl p-6 border border-zinc-800 space-y-6">
              {/* Stats */}
              <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
                {[
                  { label: 'Duration', value: `${Math.floor(analysis.duration / 60)}:${String(Math.floor(analysis.duration % 60)).padStart(2, '0')}` },
                  { label: 'Segments', value: analysis.segments.length },
                  { label: 'Peak Intensity', value: `${Math.round(analysis.peakRms * 100)}%` },
                  { label: 'Powerful Sections', value: powerfulCount },
                ].map((stat) => (
                  <div key={stat.label} className="bg-zinc-800 rounded-lg p-3 text-center">
                    <p className="text-xl font-bold text-amber-400">{stat.value}</p>
                    <p className="text-xs text-zinc-500 mt-1">{stat.label}</p>
                  </div>
                ))}
              </div>

              <IntensityTimeline segments={analysis.segments} totalDuration={analysis.duration} />
              <SegmentList segments={analysis.segments} />
            </div>
          )}
        </section>
      )}

      {/* STEP: Prepare */}
      {step === 'prepare' && analysis && (
        <section className="space-y-4">
          <div className="h-px bg-zinc-800" />
          <h2 className="text-sm font-semibold text-zinc-400 uppercase tracking-wider">
            4. Prepare Lip Sync Job
          </h2>
          <div className="bg-zinc-900 rounded-xl p-6 border border-zinc-800 space-y-4">
            <p className="text-sm text-zinc-400">
              A job object will be created with your avatar, audio, all segments, intensity scores, and gospel lip-sync hints.
              Status will be set to <span className="text-amber-400 font-mono">pending</span>.
            </p>
            <div className="grid grid-cols-1 md:grid-cols-3 gap-3 text-xs">
              {[
                { icon: '🖼️', label: 'Avatar', value: files.avatarName },
                { icon: '🎵', label: 'Audio', value: files.audioName },
                { icon: '📋', label: 'Segments', value: `${analysis.segments.length} clips` },
              ].map((item) => (
                <div key={item.label} className="bg-zinc-800 rounded-lg p-3 flex items-center gap-2">
                  <span>{item.icon}</span>
                  <div className="min-w-0">
                    <p className="text-zinc-500">{item.label}</p>
                    <p className="text-zinc-300 truncate">{item.value}</p>
                  </div>
                </div>
              ))}
            </div>
            <button
              onClick={handlePrepare}
              disabled={preparing}
              className="w-full py-4 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-400 hover:to-orange-400 disabled:from-zinc-800 disabled:to-zinc-800 disabled:text-zinc-600 text-black font-bold text-base transition-all"
            >
              {preparing ? 'Preparing…' : '🚀 Prepare Lip Sync Job →'}
            </button>
          </div>
        </section>
      )}
    </div>
  );
}
