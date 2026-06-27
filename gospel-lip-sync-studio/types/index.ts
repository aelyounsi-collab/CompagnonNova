export type IntensityLevel = 'soft' | 'medium' | 'powerful';

export interface AudioSegment {
  index: number;
  startTime: number;
  endTime: number;
  duration: number;
  rms: number;
  intensity: IntensityLevel;
}

export interface AudioAnalysis {
  duration: number;
  sampleRate: number;
  segments: AudioSegment[];
  overallRms: number;
  peakRms: number;
  analysisVersion: string;
  analyzedAt: string;
}

export type JobStatus = 'pending' | 'analyzing' | 'ready' | 'processing' | 'completed' | 'failed';

export type LipSyncEngine = 'musetalk' | 'liveportrait' | 'syncso' | 'none';

export interface LipSyncJob {
  id: string;
  createdAt: string;
  updatedAt: string;
  status: JobStatus;
  avatarPath: string;
  audioPath: string;
  avatarOriginalName: string;
  audioOriginalName: string;
  analysis: AudioAnalysis | null;
  engine: LipSyncEngine;
  outputPath: string | null;
  error: string | null;
  progress: number;
}

export interface UploadResponse {
  success: boolean;
  avatarPath?: string;
  audioPath?: string;
  error?: string;
}

export interface AnalysisResponse {
  success: boolean;
  analysis?: AudioAnalysis;
  error?: string;
}

export interface JobCreateResponse {
  success: boolean;
  job?: LipSyncJob;
  error?: string;
}

export interface JobStatusResponse {
  success: boolean;
  job?: LipSyncJob;
  error?: string;
}
