if(window.Sentry) {
    Sentry.init({
        dsn: "https://225d03b42d813e45a48dd6d7a0eac641@o125145.ingest.us.sentry.io/4509935701393408",
        integrations: [
            Sentry.browserTracingIntegration(),
            Sentry.replayIntegration()
        ],
        tracesSampleRate: 1.0, // My account has the budget LOL
        replaysSessionSampleRate: 0.0, // Useless
        replaysOnErrorSampleRate: 1.0, // Might have enough budget
        sendDefaultPii: true,
        enableLogs: false, // I'll flip this on when frontend started working
        tunnel: "/api/tunnel.php",
        ignoreErrors: [
            "jQuery",
            "Failed to fetch",
            "EADDRINUSE"
        ]
    });
    console.log("Sentry Loaded Done ^w^");
}