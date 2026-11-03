package com.kpr.portal;

import java.sql.Timestamp;

/** One row of student_files (a JavaBean so JSP/EL can read it as ${f.originalName}). */
public class StudentFile {
    private int id;
    private String category;
    private String originalName;
    private String fileType;
    private int fileSize;
    private Timestamp uploadedAt;

    public int getId() { return id; }
    public void setId(int id) { this.id = id; }
    public String getCategory() { return category; }
    public void setCategory(String category) { this.category = category; }
    public String getOriginalName() { return originalName; }
    public void setOriginalName(String originalName) { this.originalName = originalName; }
    public String getFileType() { return fileType; }
    public void setFileType(String fileType) { this.fileType = fileType; }
    public int getFileSize() { return fileSize; }
    public void setFileSize(int fileSize) { this.fileSize = fileSize; }
    public Timestamp getUploadedAt() { return uploadedAt; }
    public void setUploadedAt(Timestamp uploadedAt) { this.uploadedAt = uploadedAt; }

    /** Size in KB for display. */
    public String getSizeKb() { return String.format("%.1f", fileSize / 1024.0); }
    /** True when the browser can show it inside an <img> tag. */
    public boolean isImage() { return fileType != null && fileType.startsWith("image/"); }
}
