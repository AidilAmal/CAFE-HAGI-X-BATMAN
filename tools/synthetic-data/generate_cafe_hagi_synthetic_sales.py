"""
Generate synthetic Cafe Hagi sales history for ML training.

Output:
1. synthetic_output/sales_history_ml.csv
   - Flat table, easiest for Pandas / FastAPI AI Engine training.

2. synthetic_output/customer_orders.csv
   - Matches Laravel table: customer_orders.

3. synthetic_output/customer_order_items.csv
   - Matches Laravel table: customer_order_items.

4. synthetic_output/menus_synthetic.csv
   - Menu lookup/master reference. Import/add missing menus before importing order items
     if your MySQL `menus` table does not have menu IDs 1-10.

Install:
    pip install pandas faker

Run:
    python generate_cafe_hagi_synthetic_sales.py
"""

from __future__ import annotations

import random
from datetime import date, datetime, time, timedelta
from pathlib import Path
from typing import Dict, List

import pandas as pd
from faker import Faker

# =========================
# CONFIG
# =========================
SEED = 42
TARGET_ROWS = 5_000
DAYS_BACK = 180
OUTPUT_DIR = Path("synthetic_output")

# Pattern config
# Weekend fill weight sengaja 2.2, karena ada 3 forced bestseller rows setiap hari.
# Hasil akhirnya biasanya mendekati 2x avg sales/day dibanding weekday.
WEEKEND_WEIGHT = 2.2
COFFEE_RUSH_START_HOUR = 12
COFFEE_RUSH_END_HOUR = 15  # inclusive: 12:00 sampai 15:59
COFFEE_RUSH_MULTIPLIER = 3.8

random.seed(SEED)
Faker.seed(SEED)
fake = Faker("id_ID")

# Sesuaikan menu_id dengan ID di tabel `menus` Laravel kamu.
# Project Cafe Hagi yang dicek punya menu awal:
# 1 Es Kopi Susu, 2 Americano, 3 Matcha Latte, 4 Burger Beef.
MENUS: List[Dict] = [
    {
        "menu_id": 1,
        "menu_category_id": 1,
        "menu_name": "Es Kopi Susu",
        "slug": "es-kopi-susu",
        "category": "Coffee",
        "price": 22_000,
        "is_coffee_based": 1,
        "segment": "bestseller",
        "base_weight": 18.0,
    },
    {
        "menu_id": 2,
        "menu_category_id": 1,
        "menu_name": "Americano",
        "slug": "americano",
        "category": "Coffee",
        "price": 18_000,
        "is_coffee_based": 1,
        "segment": "bestseller",
        "base_weight": 14.0,
    },
    {
        "menu_id": 3,
        "menu_category_id": 2,
        "menu_name": "Matcha Latte",
        "slug": "matcha-latte",
        "category": "Non Coffee",
        "price": 25_000,
        "is_coffee_based": 0,
        "segment": "bestseller",
        "base_weight": 13.0,
    },
    {
        "menu_id": 4,
        "menu_category_id": 3,
        "menu_name": "Burger Beef",
        "slug": "burger-beef",
        "category": "Main Course",
        "price": 32_000,
        "is_coffee_based": 0,
        "segment": "normal",
        "base_weight": 7.0,
    },
    {
        "menu_id": 5,
        "menu_category_id": 1,
        "menu_name": "Cappuccino",
        "slug": "cappuccino",
        "category": "Coffee",
        "price": 24_000,
        "is_coffee_based": 1,
        "segment": "normal",
        "base_weight": 8.5,
    },
    {
        "menu_id": 6,
        "menu_category_id": 1,
        "menu_name": "Caramel Macchiato",
        "slug": "caramel-macchiato",
        "category": "Coffee",
        "price": 28_000,
        "is_coffee_based": 1,
        "segment": "normal",
        "base_weight": 7.5,
    },
    {
        "menu_id": 7,
        "menu_category_id": 3,
        "menu_name": "Chicken Rice Bowl",
        "slug": "chicken-rice-bowl",
        "category": "Main Course",
        "price": 35_000,
        "is_coffee_based": 0,
        "segment": "normal",
        "base_weight": 6.0,
    },
    {
        "menu_id": 8,
        "menu_category_id": 3,
        "menu_name": "Chocolate Croissant",
        "slug": "chocolate-croissant",
        "category": "Main Course",
        "price": 21_000,
        "is_coffee_based": 0,
        "segment": "normal",
        "base_weight": 5.5,
    },
    {
        "menu_id": 9,
        "menu_category_id": 1,
        "menu_name": "Manual Brew V60",
        "slug": "manual-brew-v60",
        "category": "Coffee",
        "price": 30_000,
        "is_coffee_based": 1,
        "segment": "dead_stock",
        "base_weight": 0.22,
    },
    {
        "menu_id": 10,
        "menu_category_id": 2,
        "menu_name": "Lemon Tea",
        "slug": "lemon-tea",
        "category": "Non Coffee",
        "price": 17_000,
        "is_coffee_based": 0,
        "segment": "dead_stock",
        "base_weight": 0.18,
    },
]

BEST_SELLER_NAMES = {"Es Kopi Susu", "Americano", "Matcha Latte"}
DEAD_STOCK_NAMES = {"Manual Brew V60", "Lemon Tea"}
MENU_BY_NAME = {menu["menu_name"]: menu for menu in MENUS}

# Kafe diasumsikan buka 08:00-21:59.
# Ada demand umum di lunch/afternoon, tapi pola kopi akan diperkuat lagi di menu weighting.
HOUR_WEIGHTS = {
    8: 2,
    9: 4,
    10: 5,
    11: 7,
    12: 12,
    13: 13,
    14: 12,
    15: 10,
    16: 8,
    17: 6,
    18: 7,
    19: 7,
    20: 4,
    21: 3,
}


def is_weekend(d: date) -> int:
    """Python weekday: Monday=0, Sunday=6."""
    return int(d.weekday() >= 5)


def random_order_time(preferred_hour: int | None = None) -> time:
    """Generate random time within cafe opening hours."""
    if preferred_hour is None:
        hours = list(HOUR_WEIGHTS.keys())
        weights = list(HOUR_WEIGHTS.values())
        hour = random.choices(hours, weights=weights, k=1)[0]
    else:
        hour = preferred_hour

    return time(hour=hour, minute=random.randint(0, 59), second=random.randint(0, 59))


def time_block(hour: int) -> str:
    if 8 <= hour <= 10:
        return "morning"
    if 11 <= hour <= 15:
        return "lunch_coffee_rush"
    if 16 <= hour <= 18:
        return "afternoon"
    return "night"


def choose_menu(order_hour: int) -> Dict:
    """Weighted menu sampling with hidden patterns."""
    weights = []

    for menu in MENUS:
        weight = menu["base_weight"]

        # Pola jam: coffee-based menu laku keras pukul 12-15.
        if (
            menu["is_coffee_based"]
            and COFFEE_RUSH_START_HOUR <= order_hour <= COFFEE_RUSH_END_HOUR
        ):
            weight *= COFFEE_RUSH_MULTIPLIER

        # Dead-stock tetap sangat jarang walaupun punya peluang muncul.
        if menu["segment"] == "dead_stock":
            weight *= 0.45

        weights.append(weight)

    return random.choices(MENUS, weights=weights, k=1)[0]


def choose_qty(menu: Dict, weekend: int) -> int:
    """Generate item quantity. Weekend sedikit lebih banyak order rame-rame."""
    if menu["segment"] == "dead_stock":
        return random.choices([1, 2], weights=[0.92, 0.08], k=1)[0]

    if weekend:
        return random.choices([1, 2, 3, 4], weights=[0.58, 0.29, 0.10, 0.03], k=1)[0]

    return random.choices([1, 2, 3, 4], weights=[0.72, 0.20, 0.07, 0.01], k=1)[0]


def choose_status() -> str:
    # Untuk demand forecasting, nanti sebaiknya training pakai status completed saja.
    return random.choices(
        ["completed", "processing", "pending", "cancelled"],
        weights=[0.94, 0.02, 0.02, 0.02],
        k=1,
    )[0]


def build_sale_row(
    order_id: int,
    order_date: date,
    menu: Dict,
    forced_hour: int | None = None,
    forced_status: str | None = None,
) -> Dict:
    weekend = is_weekend(order_date)
    ordered_time = random_order_time(preferred_hour=forced_hour)
    ordered_at = datetime.combine(order_date, ordered_time)
    status = forced_status or choose_status()
    qty = choose_qty(menu, weekend)
    subtotal = qty * menu["price"]

    processing_at = None
    completed_at = None
    cancelled_at = None
    stock_applied_at = None

    if status in {"processing", "completed"}:
        processing_at = ordered_at + timedelta(minutes=random.randint(5, 12))

    if status == "completed":
        completed_at = ordered_at + timedelta(minutes=random.randint(15, 35))
        stock_applied_at = completed_at

    if status == "cancelled":
        cancelled_at = ordered_at + timedelta(minutes=random.randint(3, 20))

    created_at = ordered_at
    updated_at = completed_at or cancelled_at or processing_at or ordered_at

    return {
        "id": order_id,
        "order_code": f"ORD-SYN-{order_id:06d}",
        "customer_name": fake.name(),
        "total_qty": qty,
        "total_amount": subtotal,
        "status": status,
        "ordered_at": ordered_at.strftime("%Y-%m-%d %H:%M:%S"),
        "processing_at": processing_at.strftime("%Y-%m-%d %H:%M:%S") if processing_at else "",
        "completed_at": completed_at.strftime("%Y-%m-%d %H:%M:%S") if completed_at else "",
        "cancelled_at": cancelled_at.strftime("%Y-%m-%d %H:%M:%S") if cancelled_at else "",
        "stock_applied_at": stock_applied_at.strftime("%Y-%m-%d %H:%M:%S") if stock_applied_at else "",
        "created_at": created_at.strftime("%Y-%m-%d %H:%M:%S"),
        "updated_at": updated_at.strftime("%Y-%m-%d %H:%M:%S"),
        # Detail item / ML features
        "menu_id": menu["menu_id"],
        "menu_name": menu["menu_name"],
        "menu_category": menu["category"],
        "is_coffee_based": menu["is_coffee_based"],
        "menu_segment": menu["segment"],
        "qty": qty,
        "unit_price": menu["price"],
        "subtotal": subtotal,
        "order_date": order_date.strftime("%Y-%m-%d"),
        "day_of_week_num": order_date.weekday(),
        "day_of_week": order_date.strftime("%A"),
        "is_weekend": weekend,
        "hour": ordered_at.hour,
        "time_block": time_block(ordered_at.hour),
    }


def generate_sales() -> pd.DataFrame:
    end_date = date.today() - timedelta(days=1)
    start_date = end_date - timedelta(days=DAYS_BACK - 1)
    all_dates = [start_date + timedelta(days=i) for i in range(DAYS_BACK)]

    rows: List[Dict] = []
    order_id = 1

    # Pola Menu: 3 bestseller selalu laku setiap hari.
    # Forced rows ini dibuat di jam yang realistis: kopi di coffee rush, matcha random umum.
    for d in all_dates:
        for menu_name in sorted(BEST_SELLER_NAMES):
            menu = MENU_BY_NAME[menu_name]
            forced_hour = random.choice([12, 13, 14, 15]) if menu["is_coffee_based"] else None
            rows.append(
                build_sale_row(
                    order_id,
                    d,
                    menu,
                    forced_hour=forced_hour,
                    forced_status="completed",
                )
            )
            order_id += 1

    # Fill sisa sampai tepat TARGET_ROWS.
    # Pola Waktu: weekend dipilih dengan bobot lebih tinggi.
    date_weights = [WEEKEND_WEIGHT if is_weekend(d) else 1.0 for d in all_dates]

    while len(rows) < TARGET_ROWS:
        d = random.choices(all_dates, weights=date_weights, k=1)[0]
        ordered_time = random_order_time()
        menu = choose_menu(ordered_time.hour)
        rows.append(build_sale_row(order_id, d, menu, forced_hour=ordered_time.hour))
        order_id += 1

    df = pd.DataFrame(rows)
    df = df.sort_values(["ordered_at", "id"]).reset_index(drop=True)

    # Re-number setelah sort supaya id urut secara kronologis.
    df["id"] = range(1, len(df) + 1)
    df["order_code"] = df["id"].apply(lambda x: f"ORD-SYN-{x:06d}")

    return df


def export_csv(df: pd.DataFrame) -> None:
    OUTPUT_DIR.mkdir(parents=True, exist_ok=True)

    # 1) Flat ML dataset: enak untuk training Random Forest / K-Means.
    ml_columns = [
        "id",
        "order_code",
        "ordered_at",
        "order_date",
        "day_of_week_num",
        "day_of_week",
        "is_weekend",
        "hour",
        "time_block",
        "customer_name",
        "menu_id",
        "menu_name",
        "menu_category",
        "is_coffee_based",
        "menu_segment",
        "qty",
        "unit_price",
        "subtotal",
        "status",
        "created_at",
        "updated_at",
    ]
    df[ml_columns].to_csv(OUTPUT_DIR / "sales_history_ml.csv", index=False, encoding="utf-8")

    # 2) Laravel customer_orders table.
    order_columns = [
        "id",
        "order_code",
        "customer_name",
        "total_qty",
        "total_amount",
        "status",
        "ordered_at",
        "processing_at",
        "completed_at",
        "cancelled_at",
        "stock_applied_at",
        "created_at",
        "updated_at",
    ]
    df[order_columns].to_csv(OUTPUT_DIR / "customer_orders.csv", index=False, encoding="utf-8")

    # 3) Laravel customer_order_items table.
    order_items = pd.DataFrame(
        {
            "id": df["id"],
            "customer_order_id": df["id"],
            "menu_id": df["menu_id"],
            "qty": df["qty"],
            "unit_price": df["unit_price"],
            "subtotal": df["subtotal"],
            "created_at": df["created_at"],
            "updated_at": df["updated_at"],
        }
    )
    order_items.to_csv(OUTPUT_DIR / "customer_order_items.csv", index=False, encoding="utf-8")

    # 4) Menu master reference. Pakai sebagai lookup atau seed tambahan.
    now_str = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
    menus_df = pd.DataFrame(
        [
            {
                "id": menu["menu_id"],
                "menu_category_id": menu["menu_category_id"],
                "name": menu["menu_name"],
                "slug": menu["slug"],
                "price": menu["price"],
                "image": "",
                "description": f"Synthetic {menu['segment']} menu for ML training.",
                "availability_status": "available",
                "is_visible": 1,
                "created_at": now_str,
                "updated_at": now_str,
            }
            for menu in MENUS
        ]
    )
    menus_df.to_csv(OUTPUT_DIR / "menus_synthetic.csv", index=False, encoding="utf-8")

    # Optional helper SQL for flat table import.
    create_table_sql = """CREATE TABLE IF NOT EXISTS sales_history_ml (
    id BIGINT UNSIGNED PRIMARY KEY,
    order_code VARCHAR(50) NOT NULL,
    ordered_at DATETIME NOT NULL,
    order_date DATE NOT NULL,
    day_of_week_num TINYINT UNSIGNED NOT NULL,
    day_of_week VARCHAR(16) NOT NULL,
    is_weekend TINYINT(1) NOT NULL,
    hour TINYINT UNSIGNED NOT NULL,
    time_block VARCHAR(32) NOT NULL,
    customer_name VARCHAR(120) NULL,
    menu_id BIGINT UNSIGNED NOT NULL,
    menu_name VARCHAR(120) NOT NULL,
    menu_category VARCHAR(60) NOT NULL,
    is_coffee_based TINYINT(1) NOT NULL,
    menu_segment VARCHAR(32) NOT NULL,
    qty INT UNSIGNED NOT NULL,
    unit_price DECIMAL(12, 2) NOT NULL,
    subtotal DECIMAL(12, 2) NOT NULL,
    status VARCHAR(24) NOT NULL,
    created_at DATETIME NULL,
    updated_at DATETIME NULL,
    INDEX idx_sales_history_order_date (order_date),
    INDEX idx_sales_history_menu_id (menu_id),
    INDEX idx_sales_history_weekend_hour (is_weekend, hour),
    INDEX idx_sales_history_status (status)
);
"""
    (OUTPUT_DIR / "create_sales_history_ml_table.sql").write_text(create_table_sql, encoding="utf-8")


def print_quality_report(df: pd.DataFrame) -> None:
    completed = df[df["status"] == "completed"].copy()

    daily = (
        completed.groupby(["order_date", "is_weekend"], as_index=False)
        .agg(rows=("id", "count"), qty=("qty", "sum"), revenue=("subtotal", "sum"))
    )

    weekday_avg_rows = daily[daily["is_weekend"] == 0]["rows"].mean()
    weekend_avg_rows = daily[daily["is_weekend"] == 1]["rows"].mean()
    weekend_ratio = weekend_avg_rows / weekday_avg_rows

    best_seller_daily_presence = (
        completed[completed["menu_name"].isin(BEST_SELLER_NAMES)]
        .groupby("order_date")["menu_name"]
        .nunique()
    )
    days_all_bestsellers_sold = int((best_seller_daily_presence == len(BEST_SELLER_NAMES)).sum())

    coffee_by_period = (
        completed.groupby("time_block", as_index=False)
        .agg(
            total_rows=("id", "count"),
            coffee_rows=("is_coffee_based", "sum"),
        )
        .assign(coffee_share=lambda x: (x["coffee_rows"] / x["total_rows"] * 100).round(2))
    )

    menu_summary = (
        completed.groupby(["menu_name", "menu_segment"], as_index=False)
        .agg(rows=("id", "count"), qty=("qty", "sum"), revenue=("subtotal", "sum"))
        .sort_values("qty", ascending=False)
    )

    print("\n=== Synthetic Data Quality Report ===")
    print(f"Total generated rows          : {len(df):,}")
    print(f"Completed rows for ML          : {len(completed):,}")
    print(f"Avg completed rows / weekday   : {weekday_avg_rows:.2f}")
    print(f"Avg completed rows / weekend   : {weekend_avg_rows:.2f}")
    print(f"Weekend vs weekday ratio       : {weekend_ratio:.2f}x")
    print(
        f"Days where all 3 bestsellers sold: {days_all_bestsellers_sold}/{DAYS_BACK} days"
    )

    print("\n--- Coffee share by time block ---")
    print(coffee_by_period.to_string(index=False))

    print("\n--- Menu demand summary ---")
    print(menu_summary.to_string(index=False))

    print(f"\nCSV exported to: {OUTPUT_DIR.resolve()}")


def main() -> None:
    df = generate_sales()
    export_csv(df)
    print_quality_report(df)


if __name__ == "__main__":
    main()
