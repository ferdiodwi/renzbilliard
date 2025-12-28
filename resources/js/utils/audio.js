// Warning beep (single) for sessions < 5 minutes
export const playAlertSound = () => {
    const audioContext = new (window.AudioContext || window.webkitAudioContext)();

    const oscillator = audioContext.createOscillator();
    const gainNode = audioContext.createGain();

    oscillator.connect(gainNode);
    gainNode.connect(audioContext.destination);

    oscillator.type = 'sine';
    oscillator.frequency.setValueAtTime(880, audioContext.currentTime); // A5
    gainNode.gain.setValueAtTime(0.2, audioContext.currentTime);

    oscillator.start();

    setTimeout(() => {
        oscillator.stop();
        audioContext.close();
    }, 200);
}

// Expired session beep (repeating for 30 seconds)
let expiredSessionInterval = null

export const playExpiredSessionSound = () => {
    // Stop any existing alert first
    stopExpiredSessionSound()

    let beepCount = 0
    const maxBeeps = 30 // 30 beeps over 30 seconds

    const playBeep = () => {
        const audioContext = new (window.AudioContext || window.webkitAudioContext)();

        const oscillator = audioContext.createOscillator();
        const gainNode = audioContext.createGain();

        oscillator.connect(gainNode);
        gainNode.connect(audioContext.destination);

        oscillator.type = 'sine';
        oscillator.frequency.setValueAtTime(1320, audioContext.currentTime); // E6 - Very high pitch for urgency
        gainNode.gain.setValueAtTime(0.3, audioContext.currentTime); // Louder

        oscillator.start();

        setTimeout(() => {
            oscillator.stop();
            audioContext.close();
        }, 300); // Slightly longer beep
    }

    // Play first beep immediately
    playBeep()
    beepCount++

    // Play remaining beeps every 1 second
    expiredSessionInterval = setInterval(() => {
        playBeep()
        beepCount++

        if (beepCount >= maxBeeps) {
            clearInterval(expiredSessionInterval)
            expiredSessionInterval = null
        }
    }, 1000)
}

// Stop the expired session alert sound
export const stopExpiredSessionSound = () => {
    if (expiredSessionInterval) {
        clearInterval(expiredSessionInterval)
        expiredSessionInterval = null
    }
}

export const playTransactionSound = () => {
    const audioContext = new (window.AudioContext || window.webkitAudioContext)();

    // Coin sound effect using two oscillators
    const osc1 = audioContext.createOscillator();
    const osc2 = audioContext.createOscillator();
    const gainNode = audioContext.createGain();

    osc1.connect(gainNode);
    osc2.connect(gainNode);
    gainNode.connect(audioContext.destination);

    // High pitched coin sound
    osc1.type = 'sine';
    osc1.frequency.setValueAtTime(1200, audioContext.currentTime);
    osc1.frequency.exponentialRampToValueAtTime(1800, audioContext.currentTime + 0.1);

    osc2.type = 'square';
    osc2.frequency.setValueAtTime(1200, audioContext.currentTime);
    osc2.frequency.exponentialRampToValueAtTime(1800, audioContext.currentTime + 0.1);

    gainNode.gain.setValueAtTime(0.05, audioContext.currentTime);
    gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.1);

    osc1.start();
    osc2.start();

    setTimeout(() => {
        osc1.stop();
        osc2.stop();
        audioContext.close();
    }, 150);
}
