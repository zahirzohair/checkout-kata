export interface Sku {
    sku: string;
    label: string | null;
}

export interface ScannedLine {
    sku: string;
    quantity: number;
}
