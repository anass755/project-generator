<footer class="footer">
    <p>&copy; {{ date('Y') }} Docker Generator. Built with <i class="fas fa-heart"></i> using Laravel</p>
    <div class="footer-links">
        <a href="#">Documentation</a>
        <a href="#">Support</a>
        <a href="https://github.com"><i class="fab fa-github"></i> GitHub</a>
    </div>
</footer>

<style>
.footer {
    background: rgba(255, 255, 255, 0.86);
    border-top: 1px solid #d8e0ef;
    padding: 18px 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    backdrop-filter: blur(8px);
}

.footer p {
    color: #5b6780;
    font-size: 13px;
    margin: 0;
    font-weight: 500;
}

.footer i.fa-heart {
    color: #b42318;
}

.footer-links {
    display: flex;
    gap: 16px;
}

.footer-links a {
    color: #5b6780;
    text-decoration: none;
    font-size: 13px;
    font-weight: 600;
    padding: 6px 10px;
    border-radius: 999px;
    border: 1px solid transparent;
    transition: all 0.2s ease;
}

.footer-links a:hover {
    color: #173b6a;
    border-color: #d8e0ef;
    background: rgba(31, 78, 140, 0.06);
}

@media (max-width: 768px) {
    .footer {
        flex-direction: column;
        gap: 12px;
        text-align: center;
    }
}
</style>
