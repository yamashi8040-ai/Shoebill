import http from '@/api/http';

export interface StatsPeriod {
    samples: number;
    cpu: number | null;
    memory: number | null;
    players: number | null;
    peak: number | null;
}

export interface ServerStats {
    protocol: string;
    summary: Record<'today' | 'week' | 'month' | 'all', StatsPeriod>;
    online: { online: number; max: number; players: string[] } | null;
    limits: { cpu: number; memory_bytes: number };
}

// Shoebill endpoint: averages recorded by `shoebill:stats:record`.
export default async (uuid: string): Promise<ServerStats> => {
    const { data } = await http.get(`/api/client/servers/${uuid}/stats`);

    return data.attributes;
};
