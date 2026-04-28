.axiom-floating-test-page {
    background: #050b10;
    color: #ffffff;
    min-height: 100vh;
}

.axiom-floating-hero {
    position: relative;
    overflow: hidden;
    padding: 70px 20px 90px;
    min-height: 720px;
    background:
        radial-gradient(circle at center, rgba(59, 111, 224, 0.16), transparent 35%),
        radial-gradient(circle at left, rgba(58, 255, 156, 0.16), transparent 32%),
        linear-gradient(180deg, #061017 0%, #05080d 100%);
    border: 1px solid rgba(58, 255, 156, 0.25);
}

.axiom-floating-bg-glow {
    position: absolute;
    inset: 0;
    pointer-events: none;
    box-shadow:
        inset 0 0 80px rgba(56, 255, 150, 0.22),
        inset 0 0 160px rgba(56, 255, 150, 0.12);
}

.axiom-floating-stats {
    position: relative;
    z-index: 2;
    max-width: 760px;
    margin: 0 auto 25px;
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 22px 60px;
    text-align: center;
}

.axiom-floating-stats strong {
    display: block;
    font-size: 28px;
    font-weight: 900;
    line-height: 1.05;
}

.axiom-floating-stats span {
    display: block;
    margin-top: 7px;
    color: rgba(255, 255, 255, 0.55);
    font-size: 15px;
}

.axiom-floating-proof {
    position: relative;
    z-index: 2;
    text-align: center;
    color: rgba(255, 255, 255, 0.5);
    font-weight: 700;
    margin: 0 auto 50px;
    max-width: 760px;
}

.axiom-vial-stage {
    position: relative;
    z-index: 2;
    max-width: 900px;
    height: 360px;
    margin: 0 auto;
}

.axiom-floating-vial {
    position: absolute;
    width: 120px;
    height: auto;
    object-fit: contain;
    filter: drop-shadow(0 22px 24px rgba(0, 0, 0, 0.45));
    animation-name: axiomVialFloat;
    animation-timing-function: ease-in-out;
    animation-iteration-count: infinite;
    will-change: transform;
}

/* Vial positions */
.vial-1 {
    left: 4%;
    top: 145px;
    width: 112px;
    animation-duration: 3.4s;
    animation-delay: 0s;
}

.vial-2 {
    left: 24%;
    top: 130px;
    width: 118px;
    animation-duration: 3.8s;
    animation-delay: -0.8s;
}

.vial-3 {
    left: 44%;
    top: 70px;
    width: 132px;
    animation-duration: 3.2s;
    animation-delay: -1.4s;
}

.vial-4 {
    left: 64%;
    top: 105px;
    width: 122px;
    animation-duration: 3.7s;
    animation-delay: -0.4s;
}

.vial-5 {
    right: 3%;
    top: 125px;
    width: 116px;
    animation-duration: 3.5s;
    animation-delay: -1.1s;
}

@keyframes axiomVialFloat {
    0% {
        transform: translateY(0px);
    }
    50% {
        transform: translateY(-34px);
    }
    100% {
        transform: translateY(0px);
    }
}

/* Mobile */
@media (max-width: 768px) {
    .axiom-floating-hero {
        padding: 55px 16px 70px;
        min-height: 650px;
    }

    .axiom-floating-stats {
        gap: 18px 30px;
    }

    .axiom-floating-stats strong {
        font-size: 25px;
    }

    .axiom-floating-proof {
        font-size: 14px;
        margin-bottom: 35px;
    }

    .axiom-vial-stage {
        height: 330px;
    }

    .axiom-floating-vial {
        width: 82px;
    }

    .vial-1 {
        left: 2%;
        top: 150px;
        width: 78px;
    }

    .vial-2 {
        left: 22%;
        top: 120px;
        width: 82px;
    }

    .vial-3 {
        left: 42%;
        top: 70px;
        width: 92px;
    }

    .vial-4 {
        left: 63%;
        top: 118px;
        width: 84px;
    }

    .vial-5 {
        right: 1%;
        top: 135px;
        width: 80px;
    }

    @keyframes axiomVialFloat {
        0% {
            transform: translateY(0px);
        }
        50% {
            transform: translateY(-24px);
        }
        100% {
            transform: translateY(0px);
        }
    }
}

/* Accessibility: stop animation if user prefers reduced motion */
@media (prefers-reduced-motion: reduce) {
    .axiom-floating-vial {
        animation: none !important;
    }
}
