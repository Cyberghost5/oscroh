<?php

namespace App\Http\Controllers;

use App\Model\Sound;
use Illuminate\Http\Request;

class SoundsController extends Controller
{
    public function trending(Request $request)
    {
        $limit = (int) $request->get('limit', 20);

        $sounds = Sound::query()
            ->where('is_active', 1)
            ->with(['attachments' => function ($q) {
                $q->orderBy('created_at', 'asc');
            }])
            ->orderByDesc('id')
            ->limit($limit)
            ->get();

        return response()->json([
            'sounds' => $this->toSelectize($sounds),
        ]);
    }

    public function search(Request $request)
    {
        $q = trim((string) $request->get('q', ''));
        $limit = (int) $request->get('limit', 20);

        if ($q === '') {
            return response()->json(['sounds' => []]);
        }

        $sounds = Sound::query()
            ->where('is_active', 1)
            ->where(function ($qq) use ($q) {
                $qq->where('title', 'like', "%{$q}%")
                    ->orWhere('artist', 'like', "%{$q}%");
            })
            ->with(['attachments' => function ($q) {
                $q->orderBy('created_at', 'asc');
            }])
            ->orderByDesc('id')
            ->limit($limit)
            ->get();

        return response()->json([
            'sounds' => $this->toSelectize($sounds),
        ]);
    }

    private function toSelectize($sounds): array
    {
        return $sounds->map(function ($s) {
            // pick FIRST audio-like attachment; adapt if your type system differs
            $audio = $s->attachments->first(function ($att) {
                return in_array($att->type, ['audio', 'mp3', 'wav', 'm4a', 'aac', 'ogg'], true);
            });
            $url = $audio ? $audio->path : null;
            return [
                'id'     => (string) $s->id,
                'title'  => (string) $s->title,
                'artist' => (string) ($s->artist ?? ''),
                'cover'  => (string) $s->coverAttachment->path,
                'url'    => (string) ($url ?? ''),
            ];
        })->values()->all();
    }
}
