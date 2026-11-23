import React from 'react';
import useSWR from 'swr';
import tw from 'twin.macro';
import { ServerContext } from '@/state/server';
import ServerContentBlock from '@/components/elements/ServerContentBlock';
import TitledGreyBox from '@/components/elements/TitledGreyBox';
import Spinner from '@/components/elements/Spinner';
import getServerStats, { ServerStats } from '@/api/server/getServerStats';
import { bytesToString } from '@/lib/formatters';

const periods: { key: keyof ServerStats['summary']; label: string }[] = [
    { key: 'today', label: 'Today' },
    { key: 'week', label: 'Last 7 days' },
    { key: 'month', label: 'Last 30 days' },
    { key: 'all', label: 'All time' },
];

const percent = (value: number, of: number) => (of ? ` (${Math.round((value / of) * 100)}%)` : '');

export default () => {
    const uuid = ServerContext.useStoreState((state) => state.server.data!.uuid);
    const { data } = useSWR<ServerStats>([uuid, '/stats'], () => getServerStats(uuid), { refreshInterval: 60000 });

    if (!data) {
        return (
            <ServerContentBlock title={'Status'}>
                <Spinner size={'large'} centered />
            </ServerContentBlock>
        );
    }

    const hasPlayers = data.protocol !== 'none';

    return (
        <ServerContentBlock title={'Status'}>
            <TitledGreyBox title={'Averages'}>
                <div css={tw`overflow-x-auto`}>
                    <table css={tw`w-full text-sm text-left`}>
                        <thead css={tw`text-neutral-400`}>
                            <tr>
                                <th css={tw`py-2 pr-4`}>Period</th>
                                {hasPlayers && <th css={tw`py-2 pr-4`}>Avg. players</th>}
                                {hasPlayers && <th css={tw`py-2 pr-4`}>Peak players</th>}
                                <th css={tw`py-2 pr-4`}>Avg. CPU</th>
                                <th css={tw`py-2 pr-4`}>Avg. memory</th>
                            </tr>
                        </thead>
                        <tbody>
                            {periods.map(({ key, label }) => {
                                const row = data.summary[key];

                                return (
                                    <tr key={key} css={tw`border-t border-neutral-600`}>
                                        <td css={tw`py-2 pr-4 font-medium`}>{label}</td>
                                        {hasPlayers && <td css={tw`py-2 pr-4`}>{row.players?.toFixed(1) ?? '—'}</td>}
                                        {hasPlayers && <td css={tw`py-2 pr-4`}>{row.peak ?? '—'}</td>}
                                        <td css={tw`py-2 pr-4`}>
                                            {row.cpu === null
                                                ? '—'
                                                : `${row.cpu.toFixed(1)}%${percent(row.cpu, data.limits.cpu)}`}
                                        </td>
                                        <td css={tw`py-2 pr-4`}>
                                            {row.memory === null
                                                ? '—'
                                                : bytesToString(row.memory) + percent(row.memory, data.limits.memory_bytes)}
                                        </td>
                                    </tr>
                                );
                            })}
                        </tbody>
                    </table>
                </div>
                {!hasPlayers && (
                    <p css={tw`mt-4 text-xs text-neutral-400`}>
                        This game has no player query, so only CPU and memory are tracked.
                    </p>
                )}
            </TitledGreyBox>
            {hasPlayers && (
                <TitledGreyBox
                    title={`Online now: ${data.online?.online ?? 0} / ${data.online?.max ?? 0}`}
                    css={tw`mt-4`}
                >
                    {data.online?.players.length ? (
                        <div css={tw`flex flex-wrap gap-2`}>
                            {data.online.players.map((name) => (
                                <span key={name} css={tw`rounded bg-neutral-600 px-2 py-1 text-xs`}>
                                    {name}
                                </span>
                            ))}
                        </div>
                    ) : (
                        <p css={tw`text-sm text-neutral-400`}>This game does not share player names.</p>
                    )}
                </TitledGreyBox>
            )}
        </ServerContentBlock>
    );
};
