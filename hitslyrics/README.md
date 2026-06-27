# 🎙️ HitsLyrics — Lip Sync Studio

A professional web application for creating AI-powered lip-sync videos — gospel, R&B, pop, and more.  
Upload a portrait image and an audio file — the app analyzes vocal intensity, segments the track, and prepares a lip-sync job ready for AI engine connection.

---

## Features (V1)

- **Avatar upload** — portrait image with 9:16 preview
- **Audio upload** — MP3, WAV, AAC, OGG, FLAC support
- **Audio analysis** — duration, RMS curve, 10–15 second segmentation
- **Intensity classification** — soft / medium / powerful per segment
- **Visual timeline** — intensity bar chart over the full track
- **Job creation** — structured JSON job with avatar, audio, segments, gospel lip-sync hints
- **Job processing page** — engine selection UI, progress display, segment list
- **Dark modern UI** — Tailwind CSS, zinc + amber palette

---

## Tech Stack

| Layer       | Technology                          |
|-------------|-------------------------------------|
| Framework   | Next.js 15 (App Router)             |
| Language    | TypeScript                          |
| Styling     | Tailwind CSS v4                     |
| Backend     | Next.js API Routes (Node.js)        |
| Storage     | Local filesystem (`public/uploads`) |
| Audio       | FFmpeg-ready (Phase 2)              |

---

## Installation

### Prerequisites

- Node.js 18+
- npm 9+

### Steps

```bash
# 1. Clone or navigate to the project
cd gospel-lip-sync-studio

# 2. Install dependencies
npm install

# 3. Copy environment file
cp .env.example .env.local

# 4. Start development server
npm run dev
```

Open [http://localhost:3000](http://localhost:3000) in your browser.

---

## Project Structure

```
gospel-lip-sync-studio/
├── app/
│   ├── api/
│   │   ├── upload/route.ts       # File upload endpoint
│   │   ├── analyze/route.ts      # Audio analysis endpoint
│   │   └── jobs/
│   │       ├── route.ts          # Create/list jobs
│   │       └── [id]/route.ts     # Get/update single job
│   ├── jobs/[id]/page.tsx        # Job detail / processing page
│   ├── layout.tsx
│   ├── page.tsx                  # Home / studio workflow
│   └── globals.css
├── components/
│   ├── StudioWorkflow.tsx        # Main multi-step workflow
│   └── ui/
│       ├── UploadCard.tsx        # Drag-and-drop upload zone
│       ├── AvatarPreview.tsx     # 9:16 portrait preview
│       ├── AudioPlayer.tsx       # Custom audio player
│       ├── SegmentList.tsx       # Segment intensity list
│       ├── IntensityTimeline.tsx # Visual volume bar chart
│       └── StepIndicator.tsx     # Progress steps
├── lib/
│   ├── audio/
│   │   └── analyzer.ts          # Audio analysis + gospel hints
│   └── jobs/
│       └── store.ts             # Job persistence (JSON file, V1)
├── types/
│   └── index.ts                 # TypeScript types
├── public/
│   └── uploads/                 # Local file storage
├── .env.example
└── README.md
```

---

## API Endpoints

| Method | Endpoint              | Description                  |
|--------|-----------------------|------------------------------|
| POST   | `/api/upload`         | Upload avatar + audio files  |
| POST   | `/api/analyze`        | Analyze audio file           |
| POST   | `/api/jobs`           | Create lip-sync job          |
| GET    | `/api/jobs`           | List all jobs                |
| GET    | `/api/jobs/:id`       | Get job by ID                |
| PATCH  | `/api/jobs/:id`       | Update job status/progress   |

---

## Gospel-Specific Lip Sync Logic

The app classifies each audio segment by intensity:

| Intensity | RMS Ratio | Lip Sync Effect (Phase 2+)                              |
|-----------|-----------|----------------------------------------------------------|
| Soft      | < 40%     | Small mouth, slow jaw, serene expression, gentle vowels  |
| Medium    | 40–70%    | Normal mouth, regular jaw, passionate expression         |
| Powerful  | ≥ 70%     | Wide mouth, fast jaw, ecstatic expression, sustained vowels |

These hints are saved in the job JSON and will be passed to the lip-sync engine in Phase 2+.

---

## Roadmap

### Phase 1 — Current (V1)
- [x] Upload workflow
- [x] Audio analysis and segmentation
- [x] Job creation with intensity data
- [x] Job processing page UI
- [ ] Real FFmpeg audio decoding (RMS from PCM)

### Phase 2 — MuseTalk
- [ ] Connect MuseTalk open-source lip-sync engine (local GPU)
- [ ] Real PCM audio analysis via ffprobe
- [ ] Video generation from avatar + audio segments

### Phase 3 — LivePortrait
- [ ] Add LivePortrait for expression control
- [ ] Map gospel intensity hints to facial expression parameters
- [ ] Wider mouth / stronger jaw for powerful sections

### Phase 4 — Sync.so API
- [ ] Add Sync.so cloud API as alternative engine
- [ ] API key management

### Phase 5 — Export
- [ ] Merge all segments into final video
- [ ] Export 1080×1920 MP4 (9:16)
- [ ] Optimized for TikTok, YouTube Shorts, Instagram Reels
- [ ] Watermark / branding options

---

## Production Considerations

Before deploying to production:

1. **Replace file storage** with S3, Cloudflare R2, or similar
2. **Replace job store** (`_jobs.json`) with PostgreSQL or Redis
3. **Add authentication** (NextAuth.js or Clerk)
4. **Add rate limiting** on upload and analyze endpoints
5. **Move FFmpeg processing** to a background queue (BullMQ + Redis)
6. **Add GPU worker** for MuseTalk / LivePortrait processing

---

## License

MIT — see LICENSE file.
