<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $coupon->headline }} - Coupon</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #f3f4f6;
            color: #111827;
            font-family: Arial, Helvetica, sans-serif;
        }

        .page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 32px;
        }

        .coupon {
            width: min(100%, 680px);
            background: #ffffff;
            border: 1px solid #d1d5db;
            border-radius: 18px;
            padding: 18px;
            box-shadow: 0 18px 50px rgba(15, 23, 42, .14);
        }

        .coupon-inner {
            border: 3px dashed #1f2937;
            border-radius: 14px;
            min-height: 320px;
            padding: 46px 32px;
            text-align: center;
        }

        .brand {
            color: #1e40af;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: .18em;
            margin: 0 0 28px;
            text-transform: uppercase;
        }

        .kicker {
            color: #6b7280;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: .14em;
            margin: 0 0 10px;
            text-transform: uppercase;
        }

        h1 {
            font-size: clamp(36px, 8vw, 64px);
            line-height: .95;
            margin: 0;
        }

        .description {
            color: #374151;
            font-size: 20px;
            line-height: 1.4;
            margin: 22px auto 0;
            max-width: 460px;
        }

        .fine-print {
            border-top: 1px dashed #9ca3af;
            color: #6b7280;
            font-size: 13px;
            line-height: 1.5;
            margin: 30px auto 0;
            max-width: 440px;
            padding-top: 18px;
        }

        .expires {
            color: #92400e;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: .12em;
            margin: 22px 0 0;
            text-transform: uppercase;
        }

        .actions {
            margin-top: 22px;
            text-align: center;
        }

        button {
            appearance: none;
            background: #1d4ed8;
            border: 0;
            border-radius: 10px;
            color: #ffffff;
            cursor: pointer;
            font-size: 14px;
            font-weight: 700;
            padding: 12px 18px;
        }

        @media print {
            body {
                background: #ffffff;
            }

            .page {
                min-height: 0;
                padding: 0;
            }

            .coupon {
                border: 0;
                border-radius: 0;
                box-shadow: none;
                max-width: none;
                width: 100%;
            }

            .actions {
                display: none;
            }
        }
    </style>
</head>
<body onload="window.print()">
    <main class="page">
        <div>
            <article class="coupon" aria-label="Printable coupon">
                <div class="coupon-inner">
                    <p class="brand">Poseidon</p>
                    @if($coupon->kicker)
                        <p class="kicker">{{ $coupon->kicker }}</p>
                    @endif
                    <h1>{{ $coupon->headline }}</h1>
                    @if($coupon->description)
                        <p class="description">{{ $coupon->description }}</p>
                    @endif
                    @if($coupon->resolvedExpiryLabel())
                        <p class="expires">Expires {{ $coupon->resolvedExpiryLabel() }}</p>
                    @endif
                    @if($coupon->fine_print)
                        <p class="fine-print">{{ $coupon->fine_print }}</p>
                    @endif
                </div>
            </article>

            <div class="actions">
                <button type="button" onclick="window.print()">Print coupon</button>
            </div>
        </div>
    </main>
</body>
</html>
