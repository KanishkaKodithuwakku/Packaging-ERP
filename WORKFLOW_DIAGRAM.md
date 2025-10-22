# 🔄 GRN & Material Request Workflow Diagrams

## Overview
This document contains workflow diagrams for the GRN (Goods Receipt Note) and Material Request processes in the Packaging ERP system.

---

## 📦 GRN (Goods Receipt Note) Workflow

```mermaid
graph TD
    A[Supplier Order Created] --> B[Materials Delivered]
    B --> C[GRN Created]
    C --> D[GRN Items Added]
    D --> E[GRN Status: Pending]
    E --> F[Process to Stock Button]
    F --> G[Select Costing Method]
    G --> H[FIFO/LIFO Processing]
    H --> I[Inventory Transactions Created]
    I --> J[Inventory Records Updated]
    J --> K[Inventory Layers Created]
    K --> L[GRN Status: Processed]
    L --> M[Stock Available for Production]
    
    style A fill:#e1f5fe
    style M fill:#c8e6c9
    style L fill:#fff3e0
```

---

## 📝 Material Request Workflow

```mermaid
graph TD
    A[Production Order Created] --> B[Material Request Created]
    B --> C[MR Status: Pending]
    C --> D[MR Approval]
    D --> E[Check Available Stock]
    E --> F{Stock Available?}
    F -->|Yes| G[Issue Materials]
    F -->|No| H[Create Purchase Order]
    G --> I[Inventory Transaction: Consume]
    I --> J[Update Inventory Levels]
    J --> K[Update Inventory Layers]
    K --> L[MR Status: Issued]
    L --> M[Materials Ready for Production]
    H --> N[Wait for New Stock]
    N --> E
    
    style A fill:#e1f5fe
    style M fill:#c8e6c9
    style L fill:#fff3e0
    style H fill:#ffebee
```

---

## 🏭 Complete Production Workflow

```mermaid
graph TD
    A[Customer Order] --> B[Job Order Created]
    B --> C[Production Order Created]
    C --> D[Raw Materials Required]
    D --> E[Material Request Created]
    E --> F[Materials Issued]
    F --> G[Production Started]
    G --> H[WIP Inventory Created]
    H --> I[Production Complete]
    I --> J[Finished Goods Created]
    J --> K[FG Inventory Updated]
    K --> L[Delivery Note Created]
    L --> M[Customer Delivery]
    
    style A fill:#e1f5fe
    style M fill:#c8e6c9
    style J fill:#fff3e0
```

---

## 📊 Inventory Flow Diagram

```mermaid
graph LR
    A[RAW Materials] --> B[WIP Processing]
    B --> C[Finished Goods]
    C --> D[Customer Delivery]
    
    E[GRN Receipt] --> A
    F[Material Request] --> B
    G[Production Complete] --> C
    H[Delivery Note] --> D
    
    style A fill:#ffcdd2
    style B fill:#fff9c4
    style C fill:#c8e6c9
    style D fill:#e1f5fe
```

---

## 🔄 Transaction Types Flow

```mermaid
graph TD
    A[Transaction Types] --> B[Receipt]
    A --> C[Consume]
    A --> D[Produce]
    A --> E[Delivery]
    
    B --> F[GRN Processing]
    C --> G[Material Request]
    D --> H[Production Complete]
    E --> I[Customer Shipment]
    
    F --> J[RAW Category]
    G --> K[RAW to WIP]
    H --> L[WIP to FG]
    I --> M[FG to Customer]
    
    style B fill:#c8e6c9
    style C fill:#ffcdd2
    style D fill:#fff9c4
    style E fill:#e1f5fe
```

---

## 💰 Costing Methods Flow

```mermaid
graph TD
    A[Inventory Receipt] --> B{Costing Method}
    B -->|FIFO| C[First In, First Out]
    B -->|LIFO| D[Last In, First Out]
    
    C --> E[Oldest Layer First]
    D --> F[Newest Layer First]
    
    E --> G[Layer Consumption]
    F --> G
    
    G --> H[Cost Calculation]
    H --> I[Transaction Record]
    
    style C fill:#c8e6c9
    style D fill:#ffcdd2
    style H fill:#fff3e0
```

---

## 🏢 Warehouse Management Flow

```mermaid
graph TD
    A[Warehouse System] --> B[RAW Warehouse]
    A --> C[WIP Warehouse]
    A --> D[FG Warehouse]
    
    B --> E[Raw Materials Storage]
    C --> F[Work in Progress]
    D --> G[Finished Goods Storage]
    
    E --> H[GRN Receipt]
    F --> I[Production Processing]
    G --> J[Customer Delivery]
    
    style B fill:#ffcdd2
    style C fill:#fff9c4
    style D fill:#c8e6c9
```

---

## 📈 Reporting & Analytics Flow

```mermaid
graph TD
    A[Inventory Data] --> B[Stock Movement Report]
    A --> C[Inventory Dashboard]
    A --> D[Aging Report]
    A --> E[Cost Analysis]
    
    B --> F[Transaction History]
    C --> G[Current Stock Levels]
    D --> H[Stock Age Analysis]
    E --> I[FIFO/LIFO Costs]
    
    F --> J[Export CSV]
    G --> K[Real-time Updates]
    H --> L[Optimization Insights]
    I --> M[Cost Tracking]
    
    style A fill:#e1f5fe
    style J fill:#c8e6c9
    style K fill:#fff3e0
```

---

## 🔧 System Integration Flow

```mermaid
graph TD
    A[Packaging ERP System] --> B[GRN Management]
    A --> C[Material Request System]
    A --> D[Inventory Management]
    A --> E[Production Management]
    A --> F[Reporting System]
    
    B --> G[Supplier Integration]
    C --> H[Production Integration]
    D --> I[Costing Integration]
    E --> J[Job Order Integration]
    F --> K[Analytics Integration]
    
    G --> L[Purchase Orders]
    H --> M[Work Orders]
    I --> N[FIFO/LIFO]
    J --> O[Production Orders]
    K --> P[Business Intelligence]
    
    style A fill:#e1f5fe
    style L fill:#c8e6c9
    style P fill:#fff3e0
```

---

## 📋 Status Flow Diagram

```mermaid
stateDiagram-v2
    [*] --> Pending: GRN Created
    Pending --> Processing: Process to Stock
    Processing --> Processed: Stock Updated
    Processed --> [*]
    
    [*] --> Pending: MR Created
    Pending --> Approved: Manager Approval
    Approved --> Issued: Materials Released
    Issued --> Completed: Production Done
    Completed --> [*]
    
    note right of Processing: FIFO/LIFO Costing
    note right of Issued: Inventory Updated
```

---

## 🎯 Key Workflow Benefits

1. **Visual Process Understanding**: Clear flow from start to finish
2. **Decision Points**: Shows where approvals and checks occur
3. **Integration Points**: How different systems work together
4. **Status Tracking**: Current state of each process
5. **Error Handling**: What happens when issues occur

---

## 📊 Workflow Metrics

- **GRN Processing Time**: Average time from receipt to stock
- **Material Request Fulfillment**: Time from request to issue
- **Inventory Turnover**: How quickly stock moves through system
- **Cost Accuracy**: FIFO/LIFO costing precision
- **Traceability**: Complete audit trail maintenance

These workflow diagrams provide a **comprehensive visual guide** to understanding how GRN and Material Request processes work in your Packaging ERP system! 🚀
