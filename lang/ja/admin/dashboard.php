<?php

return [
    'heading' => ':app へようこそ！',
    'version' => 'バージョン: :version',
    'advanced' => '詳細設定',
    'server' => 'サーバー',
    'user' => 'ユーザー',
    'sections' => [
        'intro-developers' => [
            'heading' => '開発者向け情報',
            'content' => '開発版をお試しいただき、ありがとうございます！',
            'extra_note' => 'もし問題が発生した場合は、GitHubでご報告ください。',
            'button_issues' => 'Issueを作成',
            'button_features' => '機能について議論する',
        ],
        'intro-update-available' => [
            'heading' => 'アップデート利用可能',
            'content' => ':latestVersion が利用可能になりました！パネルを更新するには、ドキュメントをご覧ください。',
            'content_container' => ':latestVersion が利用可能です！新しいイメージを取得してコンテナを再作成し、パネルを更新してください。',
            'button_changelog' => '新着情報',
            'button_guide' => '更新ガイド',
        ],
        'intro-version-unavailable' => [
            'heading' => 'バージョンを確認できません',
            'content' => '現在 :version を使用しています。GitHub から最新バージョンを取得できなかったため、更新があるかどうか分かりません。',
        ],
        'intro-no-update' => [
            'heading' => 'お使いのパネルは最新の状態です',
            'content' => '現在ご利用中のバージョンは :version です。パネルは最新の状態です！',
        ],
        'intro-first-node' => [
            'heading' => 'ノードが検出されません',
            'content' => 'ノードがまだ設定されていないようです。ご安心ください。アクションボタンをクリックするだけで、最初のノードを作成できます！',
            'extra_note' => 'もし問題が発生した場合は、GitHubでご報告ください。',
            'button_label' => 'Pelicanで最初のノードを作成',
        ],
        'intro-help' => [
            'heading' => 'お困りですか？',
            'content' => 'まずドキュメントをご確認ください！それでもお困りの場合は、Discordサーバーにアクセスしてください！',
            'button_docs' => 'ドキュメントを読む',
        ],
    ],
];
