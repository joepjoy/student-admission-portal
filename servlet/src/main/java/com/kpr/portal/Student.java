package com.kpr.portal;

import java.util.ArrayList;
import java.util.List;

/** One row of students plus the list of its uploaded files. */
public class Student {
    private int id;
    private String name;
    private String rollNo;
    private String email;
    private String course;
    private final List<StudentFile> files = new ArrayList<>();

    public int getId() { return id; }
    public void setId(int id) { this.id = id; }
    public String getName() { return name; }
    public void setName(String name) { this.name = name; }
    public String getRollNo() { return rollNo; }
    public void setRollNo(String rollNo) { this.rollNo = rollNo; }
    public String getEmail() { return email; }
    public void setEmail(String email) { this.email = email; }
    public String getCourse() { return course; }
    public void setCourse(String course) { this.course = course; }
    public List<StudentFile> getFiles() { return files; }

    /** First profile image, or null - used by the JSP's conditional logic. */
    public StudentFile getProfile() {
        for (StudentFile f : files) if ("profile".equals(f.getCategory())) return f;
        return null;
    }
}
