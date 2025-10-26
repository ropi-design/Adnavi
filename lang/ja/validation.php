<?php

return [
    /*
    |--------------------------------------------------------------------------
    | バリデーション言語行
    |--------------------------------------------------------------------------
    |
    | 以下の言語行はバリデータークラスにより使用されるデフォルトのエラー
    | メッセージです。サイズルールのようにいくつかのバリデーションを
    | 持っているものもあります。メッセージはご自由に調整してください。
    |
    */

    'accepted' => ':attributeを承認してください。',
    'accepted_if' => ':otherが:valueの場合、:attributeを承認してください。',
    'active_url' => ':attributeが有効なURLではありません。',
    'after' => ':attributeには、:dateより後の日付を指定してください。',
    'after_or_equal' => ':attributeには、:date以降の日付を指定してください。',
    'alpha' => ':attributeには、アルファベッドのみ使用できます。',
    'alpha_dash' => ":attributeには、英数字('A-Z','a-z','0-9')とハイフンと下線('-','_')が使用できます。",
    'alpha_num' => ":attributeには、英数字('A-Z','a-z','0-9')が使用できます。",
    'array' => ':attributeには、配列を指定してください。',
    'ascii' => ':attributeには、半角英数字と記号のみ使用できます。',
    'before' => ':attributeには、:dateより前の日付を指定してください。',
    'before_or_equal' => ':attributeには、:date以前の日付を指定してください。',
    'between' => [
        'array' => ':attributeの項目は、:min個から:max個にしてください。',
        'file' => ':attributeには、:min KBから:max KBまでのサイズのファイルを指定してください。',
        'numeric' => ':attributeには、:minから、:maxまでの数字を指定してください。',
        'string' => ':attributeは、:min文字から:max文字にしてください。',
    ],
    'boolean' => ':attributeには、trueかfalseを指定してください。',
    'can' => ':attributeに許可されていない値が含まれています。',
    'confirmed' => ':attributeと確認フィールドが一致していません。',
    'contains' => ':attributeに必須の値が含まれていません。',
    'current_password' => '現在のパスワードが正しくありません。',
    'date' => ':attributeには、有効な日付を指定してください。',
    'date_equals' => ':attributeには、:dateと同じ日付を指定してください。',
    'date_format' => ':attributeの形式は、:formatと合いません。',
    'decimal' => ':attributeには、小数点以下:decimal桁の数字を指定してください。',
    'declined' => ':attributeを拒否してください。',
    'declined_if' => ':otherが:valueの場合、:attributeを拒否してください。',
    'different' => ':attributeと:otherには、異なるものを指定してください。',
    'digits' => ':attributeは、:digits桁にしてください。',
    'digits_between' => ':attributeは、:min桁から:max桁にしてください。',
    'dimensions' => ':attributeの画像サイズが無効です。',
    'distinct' => ':attributeの値が重複しています。',
    'doesnt_end_with' => ':attributeは、次のいずれかで終わることはできません: :values',
    'doesnt_start_with' => ':attributeは、次のいずれかで始まることはできません: :values',
    'email' => ':attributeには、有効なメールアドレスを指定してください。',
    'ends_with' => ':attributeは、次のいずれかで終わらなければなりません: :values',
    'enum' => '選択された:attributeは、有効ではありません。',
    'exists' => '選択された:attributeは、有効ではありません。',
    'extensions' => ':attributeには、次のいずれかの拡張子のファイルを指定してください: :values',
    'file' => ':attributeには、ファイルを指定してください。',
    'filled' => ':attributeには、値を指定してください。',
    'gt' => [
        'array' => ':attributeの項目数は、:value個より大きくなければなりません。',
        'file' => ':attributeには、:value KBより大きいファイルを指定してください。',
        'numeric' => ':attributeには、:valueより大きい数字を指定してください。',
        'string' => ':attributeは、:value文字より大きくなければなりません。',
    ],
    'gte' => [
        'array' => ':attributeの項目数は、:value個以上でなければなりません。',
        'file' => ':attributeには、:value KB以上のファイルを指定してください。',
        'numeric' => ':attributeには、:value以上の数字を指定してください。',
        'string' => ':attributeは、:value文字以上でなければなりません。',
    ],
    'hex_color' => ':attributeには、有効な16進数のカラーコードを指定してください。',
    'image' => ':attributeには、画像を指定してください。',
    'in' => '選択された:attributeは、有効ではありません。',
    'in_array' => ':attributeは、:otherに存在しません。',
    'integer' => ':attributeには、整数を指定してください。',
    'ip' => ':attributeには、有効なIPアドレスを指定してください。',
    'ipv4' => ':attributeには、有効なIPv4アドレスを指定してください。',
    'ipv6' => ':attributeには、有効なIPv6アドレスを指定してください。',
    'json' => ':attributeには、有効なJSON文字列を指定してください。',
    'list' => ':attributeはリストである必要があります。',
    'lowercase' => ':attributeは、小文字でなければなりません。',
    'lt' => [
        'array' => ':attributeの項目数は、:value個より小さくなければなりません。',
        'file' => ':attributeには、:value KBより小さいファイルを指定してください。',
        'numeric' => ':attributeには、:valueより小さい数字を指定してください。',
        'string' => ':attributeは、:value文字より小さくなければなりません。',
    ],
    'lte' => [
        'array' => ':attributeの項目数は、:value個以下でなければなりません。',
        'file' => ':attributeには、:value KB以下のファイルを指定してください。',
        'numeric' => ':attributeには、:value以下の数字を指定してください。',
        'string' => ':attributeは、:value文字以下でなければなりません。',
    ],
    'mac_address' => ':attributeには、有効なMACアドレスを指定してください。',
    'max' => [
        'array' => ':attributeの項目は、:max個以下にしてください。',
        'file' => ':attributeには、:max KB以下のファイルを指定してください。',
        'numeric' => ':attributeには、:max以下の数字を指定してください。',
        'string' => ':attributeは、:max文字以下にしてください。',
    ],
    'max_digits' => ':attributeは、:max桁以下にしてください。',
    'mimes' => ':attributeには、:valuesタイプのファイルを指定してください。',
    'mimetypes' => ':attributeには、:valuesタイプのファイルを指定してください。',
    'min' => [
        'array' => ':attributeの項目は、:min個以上にしてください。',
        'file' => ':attributeには、:min KB以上のファイルを指定してください。',
        'numeric' => ':attributeには、:min以上の数字を指定してください。',
        'string' => ':attributeは、:min文字以上にしてください。',
    ],
    'min_digits' => ':attributeは、:min桁以上にしてください。',
    'missing' => ':attributeフィールドは存在してはいけません。',
    'missing_if' => ':otherが:valueの場合、:attributeフィールドは存在してはいけません。',
    'missing_unless' => ':otherが:value以外の場合、:attributeフィールドは存在してはいけません。',
    'missing_with' => ':valuesが存在する場合、:attributeフィールドは存在してはいけません。',
    'missing_with_all' => ':valuesが全て存在する場合、:attributeフィールドは存在してはいけません。',
    'multiple_of' => ':attributeは、:valueの倍数でなければなりません。',
    'not_in' => '選択された:attributeは、有効ではありません。',
    'not_regex' => ':attributeの形式が無効です。',
    'numeric' => ':attributeには、数字を指定してください。',
    'password' => [
        'letters' => ':attributeは文字を含む必要があります。',
        'mixed' => ':attributeは大文字と小文字を含む必要があります。',
        'numbers' => ':attributeは数字を含む必要があります。',
        'symbols' => ':attributeは記号を含む必要があります。',
        'uncompromised' => '指定された:attributeはデータ漏洩に含まれています。別の:attributeを選択してください。',
    ],
    'present' => ':attributeが存在している必要があります。',
    'present_if' => ':otherが:valueの場合、:attributeが存在している必要があります。',
    'present_unless' => ':otherが:value以外の場合、:attributeが存在している必要があります。',
    'present_with' => ':valuesが存在する場合、:attributeが存在している必要があります。',
    'present_with_all' => ':valuesが全て存在する場合、:attributeが存在している必要があります。',
    'prohibited' => ':attributeフィールドは禁止されています。',
    'prohibited_if' => ':otherが:valueの場合、:attributeフィールドは禁止されています。',
    'prohibited_unless' => ':otherが:values以外の場合、:attributeフィールドは禁止されています。',
    'prohibits' => ':attributeが存在する場合、:otherは存在できません。',
    'regex' => ':attributeの形式が無効です。',
    'required' => ':attributeは、必ず指定してください。',
    'required_array_keys' => ':attributeには、:valuesのエントリを含める必要があります。',
    'required_if' => ':otherが:valueの場合、:attributeを指定してください。',
    'required_if_accepted' => ':otherが承認された場合、:attributeを指定してください。',
    'required_if_declined' => ':otherが拒否された場合、:attributeを指定してください。',
    'required_unless' => ':otherが:values以外の場合、:attributeを指定してください。',
    'required_with' => ':valuesが指定されている場合、:attributeも指定してください。',
    'required_with_all' => ':valuesが全て指定されている場合、:attributeも指定してください。',
    'required_without' => ':valuesが指定されていない場合、:attributeを指定してください。',
    'required_without_all' => ':valuesが全て指定されていない場合、:attributeを指定してください。',
    'same' => ':attributeと:otherが一致していません。',
    'size' => [
        'array' => ':attributeの項目は、:size個にしてください。',
        'file' => ':attributeには、:size KBのファイルを指定してください。',
        'numeric' => ':attributeには、:sizeを指定してください。',
        'string' => ':attributeは、:size文字にしてください。',
    ],
    'starts_with' => ':attributeは、次のいずれかで始まる必要があります: :values',
    'string' => ':attributeには、文字を指定してください。',
    'timezone' => ':attributeには、有効なタイムゾーンを指定してください。',
    'ulid' => ':attributeには、有効なULIDを指定してください。',
    'unique' => '指定の:attributeは既に使用されています。',
    'uploaded' => ':attributeのアップロードに失敗しました。',
    'uppercase' => ':attributeは、大文字でなければなりません。',
    'url' => ':attributeには、有効なURLを指定してください。',
    'uuid' => ':attributeには、有効なUUIDを指定してください。',

    /*
    |--------------------------------------------------------------------------
    | カスタムバリデーション言語行
    |--------------------------------------------------------------------------
    |
    | "attribute.rule"の規約を使ってカスタムバリデーションメッセージを指定できます。
    | これにより、特定の属性ルールに対する特定のカスタム言語行を迅速に指定できます。
    |
    */

    'custom' => [
        'email' => [
            'required' => 'メールアドレスは必須です。',
            'email' => '有効なメールアドレスを入力してください。',
            'unique' => 'このメールアドレスは既に登録されています。',
        ],
        'password' => [
            'required' => 'パスワードは必須です。',
            'min' => 'パスワードは:min文字以上で入力してください。',
            'confirmed' => 'パスワード確認が一致しません。',
        ],
        'campaign_name' => [
            'required' => 'キャンペーン名は必須です。',
            'max' => 'キャンペーン名は:max文字以内で入力してください。',
        ],
        'budget_amount' => [
            'required' => '予算金額は必須です。',
            'numeric' => '予算金額は数値で入力してください。',
            'min' => '予算金額は:min円以上で設定してください。',
        ],
        'start_date' => [
            'required' => '開始日は必須です。',
            'date' => '有効な日付を入力してください。',
            'after_or_equal' => '開始日は今日以降の日付を指定してください。',
        ],
        'end_date' => [
            'required' => '終了日は必須です。',
            'date' => '有効な日付を入力してください。',
            'after' => '終了日は開始日より後の日付を指定してください。',
        ],
        'ad_account_id' => [
            'required' => '広告アカウントを選択してください。',
            'exists' => '選択された広告アカウントが見つかりません。',
        ],
        'analytics_property_id' => [
            'required' => 'Analyticsプロパティを選択してください。',
            'exists' => '選択されたAnalyticsプロパティが見つかりません。',
        ],
        'report_type' => [
            'required' => 'レポートタイプを選択してください。',
            'in' => '有効なレポートタイプを選択してください。',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | カスタムバリデーション属性名
    |--------------------------------------------------------------------------
    |
    | 以下の言語行は、プレースホルダーをよりわかりやすい表現に置き換えるために使用されます。
    | 例えば、"email"の代わりに"メールアドレス"と表示されます。
    |
    */

    'attributes' => [
        'name' => '名前',
        'username' => 'ユーザー名',
        'email' => 'メールアドレス',
        'password' => 'パスワード',
        'password_confirmation' => 'パスワード（確認）',
        'current_password' => '現在のパスワード',
        'new_password' => '新しいパスワード',
        'phone' => '電話番号',
        'mobile' => '携帯電話番号',
        'age' => '年齢',
        'sex' => '性別',
        'gender' => '性別',
        'day' => '日',
        'month' => '月',
        'year' => '年',
        'hour' => '時',
        'minute' => '分',
        'second' => '秒',
        'title' => 'タイトル',
        'content' => '内容',
        'description' => '説明',
        'excerpt' => '抜粋',
        'date' => '日付',
        'time' => '時刻',
        'available' => '利用可能',
        'size' => 'サイズ',
        'timezone' => 'タイムゾーン',
        'message' => 'メッセージ',
        'terms' => '利用規約',

        // Google Ads関連
        'campaign_name' => 'キャンペーン名',
        'campaign_type' => 'キャンペーンタイプ',
        'campaign_status' => 'キャンペーンステータス',
        'budget_amount' => '予算金額',
        'budget_type' => '予算タイプ',
        'ad_account_id' => '広告アカウント',
        'ad_account_name' => '広告アカウント名',
        'customer_id' => '顧客ID',
        'impressions' => 'インプレッション数',
        'clicks' => 'クリック数',
        'cost' => '費用',
        'conversions' => 'コンバージョン数',
        'ctr' => 'クリック率',
        'cpc' => 'クリック単価',
        'cpa' => 'コンバージョン単価',
        'roas' => '広告費用対効果',

        // Google Analytics関連
        'analytics_property_id' => 'Analyticsプロパティ',
        'property_name' => 'プロパティ名',
        'sessions' => 'セッション数',
        'users' => 'ユーザー数',
        'pageviews' => 'ページビュー数',
        'bounce_rate' => '直帰率',
        'avg_session_duration' => '平均セッション時間',
        'conversion_rate' => 'コンバージョン率',

        // レポート関連
        'report_type' => 'レポートタイプ',
        'report_name' => 'レポート名',
        'start_date' => '開始日',
        'end_date' => '終了日',
        'period' => '期間',
        'status' => 'ステータス',

        // インサイト・施策関連
        'insight_title' => 'インサイトタイトル',
        'insight_category' => 'インサイトカテゴリ',
        'priority' => '優先度',
        'impact_score' => 'インパクトスコア',
        'confidence_score' => '信頼度スコア',
        'recommendation_title' => '施策タイトル',
        'action_type' => 'アクションタイプ',
        'estimated_impact' => '推定効果',
        'implementation_difficulty' => '実施難易度',

        // Google認証関連
        'google_account_id' => 'Googleアカウント',
        'access_token' => 'アクセストークン',
        'refresh_token' => 'リフレッシュトークン',
        'token_expires_at' => 'トークン有効期限',
    ],
];
